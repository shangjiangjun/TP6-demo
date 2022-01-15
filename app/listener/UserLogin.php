<?php
declare (strict_types = 1);

namespace app\listener;

class UserLogin
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($user)   // $event 为参数名
    {
        // 事件监听处理
        dump($user->toArray());
    }
}
