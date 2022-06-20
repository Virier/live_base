<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Middleware;

use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandledDebugMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $time = microtime(true);
        try {
            $response = $handler->handle($request);
        } catch (\Throwable $exception) {
            throw $exception;
        } finally {
            $logger = $this->container->get(LoggerFactory::class)->get('request');

            // 日志
            $time = number_format(microtime(true) - $time,7);
            $requestString = 'REQUEST:' . $request->getMethod() . ' ' . $request->getUri();
            $requestString .= 'TIME: ' . $time;
            $logger->debug($requestString);
            $requestInfo = '请求信息:' . $this->getRequestString($request);
            $logger->debug($requestInfo);
            $logger = $this->container->get(LoggerFactory::class)->get('response');
            $debug = '';
            if (isset($response)) {
                $debug .= 'RESPONSE: ' . $this->getResponseString($response);
            }
            if (isset($exception) && $exception instanceof \Throwable) {
                $debug .= 'EXCEPTION: ' . $exception->getMessage();
            }

            if ($time > 1) {
                $logger->error($debug);
            } else {
                $logger->debug($debug);
            }
        }

        return $response;
    }

    protected function getResponseString(ResponseInterface $response): string
    {
        return (string) $response->getBody();
    }

    //todo 优化
    protected function getRequestString(ServerRequestInterface $request): string
    {
        return json_encode(['请求地址: '=>$request->getMethod() . ' ' . $request->getUri(), '请求参数: '=> $request->getHeaders(), '服务器信息: '=> $request->getServerParams()]);

        $request->getServerParams();
        $request->getQueryParams();
        $result = '';
        foreach ($request->getHeaders() as $header => $values) {
            foreach ((array) $values as $value) {
                $result .= $header . ': ' . $value . PHP_EOL;
            }
        }

        if (! str_contains($request->getHeaderLine('Content-Type'), 'multipart/form-data')) {
            $result .= (string) $request->getBody();
        } else {
            $result .= 'The body contains boundary data, ignore it.';
        }

        return $result;
    }

    protected function getCustomData(): string
    {
        return '';
    }
}
