<?php
namespace app\common\exception;

use think\exception\Handle;
use think\Response;
use Throwable;

class Http extends Handle
{
    public function render($request, Throwable $e): Response
    {
        // return parent::render($request, $e);
        // 请求异常
        if ($request->isAjax()) {
            show(10001, $e->getMessage(), [], 500);
        } else {
            exit($e->getMessage());
        }

    }
}