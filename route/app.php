<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\facade\Route;


// 当路由规则不匹配，会路由到miss
Route::miss(function () {
    return show(10000);
});

// swagger 生成
Route::get('swagger', 'swagger/index');
/*Route::get('swagger', function () {
    $openApi = \OpenApi\scan(root_path().'app/api'); // OpenApi\scan('../app');
    header('Content-Type: application/json');
    echo $openApi->toJson();
});*/

Route::get('hello/:name', 'index/hello');

Route::get('user-redis-test', 'MyRedis/getUserData');
Route::get('redis-list-test', 'MyRedis/setRedisList');
