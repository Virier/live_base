<?php

namespace App\Middleware;

use App\Exception\FQException;
use App\Utils\Aes;
use App\Utils\ApiAuth;
use App\Utils\ArrayUtil;
use App\Utils\RequestHeaders;
use Hyperf\HttpMessage\Server\Request;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Co\Server;
use Exception;
use GuzzleHttp\Psr7\Utils;
use Hyperf\Context\Context;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Codec\Json;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

// 记录请求返回值所有信息到日志
class BaseAesLogMiddleware implements MiddlewareInterface
{
    private $requestHash;
    private $filterCode = 511;
    private $token = "";

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var HttpResponse
     */
    protected $response;

    protected $logger;

    public function __construct(ContainerInterface $container, HttpResponse $response, RequestInterface $request)
    {
        $this->container = $container;
        $this->request = $request;
        $this->response = $response;
        $this->logger = $container->get(LoggerFactory::class)->get('AesLog');
    }


    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $aesDriver = $this->initAesSatus();
        if (!$aesDriver) {
            return $this->originRequest($request, $handler);
        }
        try {
            return $this->encryptRequest($request, $handler);
        } catch (FQException $e) {
            if ($e->getCode() === $this->filterCode) {
                return $this->originRequest($request, $handler);
            }
        }
        return $handler->handle($request);
    }

    private function initAesSatus()
    {
        $driver = config("dev.EncryptDriver");
        if ($driver === "enable") {
            return true;
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @return RequestHeaders
     */
    private function loadRequestHeader(ServerRequestInterface $request)
    {
        $requestHeaders = new RequestHeaders();
        return $requestHeaders->dataToModel($request);
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return mixed
     * @throws Exception
     */
    private function encryptRequest(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $requestHeaders = $this->loadRequestHeader($request);
        $Aes = new Aes();
        // 监测该版本是否开启aes
        $enable = $Aes->isEnableAes($requestHeaders);
        if ($enable === false) {
            throw new FQException("encryptRequest reqeust off", $this->filterCode);
        }
        // 获取该版本的aeskey并更新
        $Aes->resetAesKey($requestHeaders);
        // 对比request加密配置
        if ($requestHeaders->encrypt !== "true") {
            throw new FQException("未知错误11002", 2);
        }
        // 解析param
        $params = $this->getParam($Aes);
        if (empty($params)) {
            throw new FQException("未知错误800", 500);
        }
        $this->WriteBeforeParam($request->getUri(), $params);
        ApiAuth::getInstance()->authTimestamp($params);
        ApiAuth::getInstance()->authSign($params, $requestHeaders);
        $request = $this->setParams($request, $params);
        $request = $this->setHeaders($request, $params);
        Context::set('http.request.parsedData', null);
        $response = $handler->handle($request);
        $payload = $this->payload($response->getBody());
        $encryptResponseData = $this->encodeAesData($payload, $Aes);
        $response = $response->withBody(new SwooleStream(Json::encode($encryptResponseData)));
        $this->WriteAfterResponse($response->getBody()->getContents());
        return $response;
    }

    /**
     * @param $payloadObject
     * @param Aes $Aes
     * @return StreamInterface
     * @throws Exception
     */
    private function encodeAesData($payloadObject, Aes $Aes)
    {
        if (isset($payloadObject->sensorsData)) {
            unset($payloadObject->sensorsData);
        }
        if (isset($payloadObject->data)) {
            $jsonData = json_encode($payloadObject->data);
            $payloadObject->data = $Aes->aesEncrypt($jsonData);
        }
        return $payloadObject;
    }

    /**
     * @param $content
     * @return array|mixed
     */
    private function payload($content)
    {
        if (empty($content)) {
            return [];
        }
        return json_decode($content);
    }

    /**
     * @param Aes $Aes
     * @return array
     * @throws Exception
     */
    private function getParam(Aes $Aes)
    {
        $result = [];
        $param = $this->request->input('data', "");
        if (empty($param)) {
            return $result;
        }
        $origin = $Aes->aesDecrypt($param);
        parse_str($origin, $result);
        $this->token = ArrayUtil::safeGet($result,'token');
        return $result;
    }

    /**
     * @param ServerRequestInterface $request
     * @param $params
     * @return ServerRequestInterface
     */
    private function setParams(ServerRequestInterface $request, $params)
    {
        if (empty($params)) {
            return $request;
        }
        if ($this->request->getMethod() == 'POST') {
            return $request->withParsedBody($params);
        } else {
            return $request->withQueryParams($params);
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param $params
     */
    private function setHeaders(ServerRequestInterface $request, $params)
    {
        $token = ArrayUtil::safeGet($params,'token');
        if (empty($token)) {
            return $request;
        }
        return $request->withAddedHeader('token', $token);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return mixed
     */
    private function originRequest(ServerRequestInterface $request, RequestHandlerInterface $handler)
    {
        $requestHeaders = $this->loadRequestHeader($request);
        $params = $this->getOriginParam($request);
        $this->setParams($request, $params);
        $this->setHeaders($request, $params);
        if ($requestHeaders->encrypt !== "false") {
            throw new FQException("未知错误11001", 1);
        }
        return $handler->handle($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return array
     */
    private function getOriginParam(ServerRequestInterface $request)
    {
        $result = [];
        if (is_array($request->getParsedBody())) {
            $data = $request->getParsedBody();
        } else {
            $data = [];
        }

        $param = array_merge($data, $request->getQueryParams());
        if (empty($param)) {
            return $result;
        }
        return $param;
    }

    /**
     * @param $requestLink
     * @param $requestParam
     */
    private function WriteBeforeParam($requestLink, $requestParam)
    {
        $this->logger->debug(sprintf('WriteBeforeParam--%s--link:%s--param:%s', $this->token, $requestLink, json_encode($requestParam)));
    }


    /**
     * @param $content
     */
    private function WriteAfterResponse($content)
    {
        $this->logger->debug(sprintf('WriteAfterResponse--%s--param:%s', $this->token, $content));
    }
}
