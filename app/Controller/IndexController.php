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
        $uid = $this->request->input('uid',0);
        $token  = $this->request->input('token','');
        return $this->response->success([
            'uid' => $uid,
            'token' => $token
        ],'返回成功');
    }


}
