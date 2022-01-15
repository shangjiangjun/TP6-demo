<?php

use think\facade\Route;

// 当路由规则不匹配，会路由到miss
Route::miss(function () {
    return show(10000);
});

// api 版本路由
//Route::any(':version/:controller/:function', ':version.:controller/:function')
//    ->allowCrossDomain([
//        'Access-Control-Allow-Origin'  => '*',
//        'Access-Control-Allow-Method'  => 'GET, POST, OPTIONS',
//        'Access-Control-Allow-Headers' => 'x-requested-with, content-type, token',
//    ]);

$version = request()->header('version');
// 默认v1 版本
if ($version == null) $version = "v1";

// Route::rule(':controller/:function', $version . '.:controller/:function');
// 用户
Route::group('user', function () {
    Route::get('', 'index');
})->prefix($version . '.user/')->pattern(['id' => '\d+']);


Route::group('', function () {
    Route::get('', 'index/index');

    // jwt 认证
    Route::get('jwt', 'OAuth/createjwt');
    Route::post('verify-jwt', 'OAuth/verifyJwt');

    // oauth2.0 密码模式
    Route::any('oauth/authorize', 'OAuth/authorize');  // header 中包含 UserClient
    Route::post('oauth/check', 'OAuth/check');  // header 中包含authorization

    // 文字列表
    Route::get('articles', 'index/articles');

})->prefix($version.'.');


//Route::rule("jwt","oauth/createjwt","get");
//Route::rule("verifyjwt","oauth/verifyjwt","post");

//Route::rule('/', 'index/index', 'get');
//Route::rule('/test-sign', 'index/testSign', 'get');