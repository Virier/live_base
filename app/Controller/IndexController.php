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
namespace App\Controller;

use Hyperf\DbConnection\Db;
use Hyperf\Utils\ApplicationContext;

class IndexController extends Controller
{
    public function index()
    {
//        return $this->response->fail(500);
        // db test
        /*$userInfo = Db::connection('default')->select('SELECT * FROM zb_member where id = 1178493;');
        return $this->response->success($userInfo);*/
        // redis test
//        $container = ApplicationContext::getContainer();
//
//        $redis = $container->get(\Redis::class);
//        $result = $redis->get('1178493');
//        return $this->response->success($result);
        $user = $this->request->input('user', 'hello world');
        $method = $this->request->getMethod();

        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }


}
