<?php
// 应用公共文件

/**
 * 数据通用方法
 */
function show($status = 0, $msg = "", $data = [], $httpStatus = 200)
{
    //如果消息提示为空，并且业务状态码定义了，那么就显示默认定义的消息提示
    if (empty($msg) && !empty(config("status." . $status))) {
        $msg = config("status." . $status);
    }

    $result = [
        'status' => $status,
        'msg'    => $msg,
        'data'  => $data
    ];

    if (request()->isAjax()) {
        return json($result, $httpStatus);
    }

    return $msg;
}

/**
 * 生成令牌
 */
function createToken()
{
    $data = request()->buildToken('__token__', 'sha1');
    return $data;
}

function shopToken()
{
    $data = createToken();
    return '<input type="hidden" name="__token__" value="' . $data . '" class="token" />';
}

//获取数组里面值
function arrayValue($array, $default, $key)
{
    if (is_array($array) && isset($array[$key])) {
        return $array[$key];
    } else {
        return $default;
    }
}

//设置数组以某个字段为键值
function arrayKeyBy($array, $key = 'id')
{
    $keys = array_column($array, $key);
    if (!empty($keys)) {
        return array_column($array, null, $key);
    } else {
        return $array;
    }
}


//返回当前的毫秒时间戳
function msectime() {
    list($msec, $sec) = explode(' ', microtime());
    $msectime =  (float)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
}

