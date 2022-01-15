<?php
declare (strict_types = 1);
// 针对参数类型，开启严格模式，进行数据类型检验，默认是弱类型检验

namespace app;

use think\App;
use think\exception\ValidateException;
use think\Validate;

/**
 * 控制器基础类
 */
abstract class BaseController
{
    /**
     * Request实例
     * @var \think\Request
     */
    protected $request;

    /**
     * 应用实例
     * @var \think\App
     */
    protected $app;

    /**
     * 是否批量验证
     * @var bool
     */
    protected $batchValidate = false;

    /**
     * 控制器中间件
     * @var array
     */
    protected $middleware = [];

    /**
     * 接口凭据
     */
    protected $user_client;

    /**
     * 构造方法
     * @access public
     * @param  App  $app  应用对象
     */
    public function __construct(App $app)
    {
        $this->app     = $app;
        $this->request = $this->app->request;
        if ($this->request->header() != null) {
            $this->user_client = $this->request->header('user-client');
        } else {
            $this->user_client = "";
        }

        // 控制器初始化
        $this->initialize();
    }

    // 初始化
    protected function initialize()
    {}

    // 方法不存在，会运行
    public function __call($name, $arguments)
    {
        return show(10000);
    }

    /**
     * 验证数据
     * @access protected
     * @param  array        $data     数据
     * @param  string|array $validate 验证器名或者验证规则数组
     * @param  array        $message  提示信息
     * @param  bool         $batch    是否批量验证
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate(array $data, $validate, array $message = [], bool $batch = false)
    {
        if (is_array($validate)) {
            $v = new Validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                [$validate, $scene] = explode('.', $validate);
            }
            $class = false !== strpos($validate, '\\') ? $validate : $this->app->parseClass('validate', $validate);
            $v     = new $class();
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }

        $v->message($message);

        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        return $v->failException(true)->check($data);
    }

    /**
     * 操作成功
     * otherData: 返回的其他参数，如：code
     */
    protected function success($code='success', $data=null, $otherData=[], array $header=[])
    {
        $message = $this->getMessage($code);
        $result = [
            'status' => true,
            'message' => $message,
            'data' => $data
        ];
        if (!empty($otherData)) {
            foreach ($otherData as $k => $v) {
                $result[$k] = $v;
            }
        }
        return json_encode($result);
    }

    /**
     * 操作失败
     */
    protected function error($code='error', $data=null, $otherData=[], array $header=[])
    {
        $message = $this->getMessage($code);
        $result = [
            'status' => true,
            'message' => $message,
            'data' => $data
        ];
        if (!empty($otherData)) {
            foreach ($otherData as $k => $v) {
                $result[$k] = $v;
            }
        }
        return json_encode($result);
    }

    /**
     * 获取消息内容
    */
    protected function getMessage($code)
    {
        $messageFileName = __DIR__ . '/common/message/zh_cn/message.php';
        if(file_exists($messageFileName)) {
            $messages = require_once $messageFileName;
        }
        return isset($messages[$code])?$messages[$code]:$code;
    }

    /**
     * 获取当前的response 输出类型
     * @access protected
     * @return string
     */
    protected function getResponseType()
    {
        return request()->isJson() || request()->isAjax() ? 'json' : 'html';
    }

}
