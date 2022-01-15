<?php

/* 证书相关 -------------------------------------------------------------------------------------*/
// 生成并导出证书
function exportSSLFile() {
    $config = [
        'digest_alg'    => 'sha512',
        'private_key_bits'  => 4096,    // 字节数： 512， 1024， 2048， 4096
        'private_key_type'  => OPENSSL_KEYTYPE_RSA  // 加密类型
    ];
    $res = openssl_pkey_new($config);
    if ($res == false) return false;

    openssl_pkey_export($res, $private_key);
    $public_key = openssl_pkey_get_details($res);
    $public_key = $public_key['key'];
    file_put_contents(app()->getRootPath() . 'public/cert_public.key', $public_key);
    file_put_contents(app()->getRootPath() . 'public/cert_private.pem', $public_key);
    openssl_free_key($res);
}

// 公钥加密，私钥解密
function authCode($string, $operation = 'E') {
    $ssl_public     = file_get_contents(app()->getRootPath() . 'public/cert_public.key');
    $ssl_private    = file_get_contents(app()->getRootPath() . 'public/cert_private.pem');
    $pi_key         = openssl_pkey_get_private($ssl_private);   // 判断私钥是否可用，返回资源id,Resource ID
    $pu_key         = openssl_pkey_get_public($ssl_public);     // 判断公钥是否可用
    if (false == ($pi_key || $pu_key)) return "证书错误";

    $data = "";
    if ($operation == 'D') {
        openssl_private_decrypt(base64_decode($string), $data, $pi_key);    // 私钥解密
    } else {
        openssl_public_encrypt($string, $data, $pu_key);        // 公钥加密
        $data = base64_encode($data);
    }
    return $data;
}

// 私钥签名
function sign($string) {
    $ssl_private = file_get_contents(app()->getRootPath() . 'public/cert_private.pem');
    $pi_key      = openssl_pkey_get_private($ssl_private);
    if (false == $pi_key) return "证书错误";

    openssl_sign($string, $signature, $pi_key); // 生成签名
    $data = base64_encode($signature);
    openssl_free_key($pi_key);  // 从内存中释放和指定的 $pi_key 相关联的密钥。
    return $data;
}

// 公钥验签
function verifySign($string, $signData) {
    $ssl_public = file_get_contents(app()->getRootPath() . 'public/cert_public.key');
    $pu_key     = openssl_pkey_get_public($ssl_public);
    if (false == $pu_key) return "证书错误";

    $verify = openssl_verify($string, base64_decode($signData), $pu_key);
    openssl_free_key($pu_key);
    return $verify;
}
/* 证书相关 -------------------------------------------------------------------------------------*/


// Gateway封装成功消息
if (!function_exists('successMessage')) {
    function successMessage($mode, $data = null)
    {
        $response = [
            'status'  => true,
            'message' => 'SUCCESS',
            'mode' => $mode,
            'data' => $data
        ];
        return json_encode($response);
    }
}

// Gateway封装错误消息
if (!function_exists('failMessage')) {
    function failMessage($code = 0, $msg = 'ERROR', $mode = 'error', $data = null)
    {
        $response = [
            'status'  => false,
            'code' => $code,
            'message' => $msg,
            'mode' => $mode,
            'data' => $data
        ];
        return json_encode($response);
    }
}