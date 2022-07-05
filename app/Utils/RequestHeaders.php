<?php

namespace App\Utils;

use Hyperf\Context\Context;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use phpDocumentor\Reflection\Utils;
use Psr\Http\Message\ServerRequestInterface;

class RequestHeaders
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $imei;

    /**
     * @var string
     */
    public $deviceId;

    /**
     * @var string
     */
    public $channel;

    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $source;

    /**
     * @var string
     */
    public $platform;

    /**
     * @var string
     */
    public $encrypt;


    public function dataToModel(ServerRequestInterface $request): static
    {
        $this->id = current($request->getHeader('id'));
        $this->imei = current($request->getHeader('imei'));
        $this->deviceId = current($request->getHeader('deviceid'));
        $this->channel = current($request->getHeader('channel'));
        $this->version = current($request->getHeader('version'));
        $this->source = current($request->getHeader('source'));
        $this->platform = current($request->getHeader('platform'));
        $this->encrypt = current($request->getHeader('encrypt'));
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatFormOs()
    {
        $pos = strpos($this->platform, "Android");
        if ($pos !== false) {
            return "Android";
        }
        $pos = strpos($this->platform, "iOS");
        if ($pos !== false) {
            return "iOS";
        }
        return "";
    }

}