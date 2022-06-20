<?php


namespace App\Exception\Handler;

use App\Kernel\Http\Response;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Psr\Http\Message\ResponseInterface;
use App\Exception\FqException;
use Hyperf\Di\Annotation\Inject;
use Throwable;

class FqExceptionHandler extends ExceptionHandler
{
    /**
     * @Inject
     * @var Response
     */
    protected $response;

    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        // 判断被捕获到的异常是希望被捕获的异常
        if ($throwable instanceof FqException) {
            // 阻止异常冒泡
            $this->stopPropagation();
            return $this->response->fail($throwable->getCode(), $throwable->getMessage());
        }

        // 交给下一个异常处理器
        return $response;

        // 或者不做处理直接屏蔽异常
    }

    /**
     * 判断该异常处理器是否要对该异常进行处理
     */
    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}