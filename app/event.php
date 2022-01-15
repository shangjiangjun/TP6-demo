<?php
// 事件定义文件
return [
    'bind'      => [
        // 事件绑定
        'UserLogin' => 'app\event\UserLogin',
    ],

    'listen'    => [
        'AppInit'  => [],
        'HttpRun'  => [],
        'HttpEnd'  => [],
        'LogLevel' => [],
        'LogWrite' => [],

        // 事件监听
        'UserLogin'    =>    ['app\listener\UserLogin'],
    ],

    'subscribe' => [
    ],
];
