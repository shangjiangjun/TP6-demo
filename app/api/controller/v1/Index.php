<?php
declare (strict_types = 1);

namespace app\api\controller\v1;

use app\BaseController;
use app\common\model\Base;
use app\common\model\User as UserModel;
use app\RedisTransfer;
use GatewayWorker\Lib\Gateway;
use think\App;
use think\exception\ValidateException;
use think\facade\Log;
use tool\Code;

class Index extends BaseController
{
    protected $redis;

    protected $request;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->redis = new RedisTransfer();
    }

    /*
     * Paramter参数的in参数
     * 1、header:参数在header头中传递
     * 2、query：参数在地址后传递如 test.com?id=1
     * 3、path:参数在rest地址中如 test.com/user/1
     * 4、cookie:参数在cookie中传递
     *
     * Schema配置
     * 1、type：指定字段类型
     * 2、default：指定字段默认值
     */


    /**
     * @OA\Get(
     *     path= "/api",
     *     tags={"用户列表信息"},
     *     summary="用户列表",
     *     @OA\Response(response="200", description="successful operation")
     * )
     */
    public function index()
    {
        if ($this->request->has('__token__')) {
            $check = $this->request->checkToken('__token__', $this->request->param());

            if (false === $check) {
                throw new ValidateException('invalid token');
            }
            $param = $this->request->param();
            dump($param);
        }

        dump(microtime());

        $token = shopToken();

        // 引用扩展类
        $code = new Code();
        $goodsNum = $code->getSn(2);

        $info = $code->getUser(1);
        dump($info->toArray());

        Log::debug('日志信息:{username}', ['username' => $info->username]);

        return view('', ['token' => $token, 'goodsNum' => $goodsNum]);
    }

    public function index2(UserModel $userModel)
    {
        $type = $this->getResponseType();
        dump($type);

        $base = Base::getBase();
        dump($base);

        $lists = $userModel->getUsers();
        dump('------------');
        dump($lists->toArray());
        dump('------------');

        // $user = User::find(1);
        // dump($user->toArray());

        // // 监听事件，传入参数
        // event('UserLogin', $user);



        // 使用Redis缓存
        // Cache::store('redis')->set('name','value',3600);
        // $data = Cache::store('redis')->get('name');
        // Cache::store('redis')->delete('name');

        // $this->redis->setByCacheValue('api', 'user', $user);
        $data = $this->redis->getByCacheValue('api', 'user');
        dump($data->toArray());

        // $this->redis->setByCacheValue('base', 'index', [1,2,3]);
        // dump($this->redis->removeCacheValue('base', 'index'));
        $data2 = $this->redis->getByCacheValue('base', 'index');
        dump($data2);

        return $this->success('success');
    }

    // 测试签名,域名应为https
    public function testSign()
    {
        $str = authCode('14123', 'E');
        echo "加密结果： {$str} <br/>";
        $disStr = authCode($str, 'D');
        echo "解密结果: {$disStr}";
    }


    /**
     * @OA\Get(
     *   path="/api/article",
     *   operationId="article",
     *   tags={"文章管理"},
     *   summary="文章列表",
     *   @OA\Parameter(name="token", in="header", description="token", @OA\Schema(type="string", default="123456")),
     *   @OA\Parameter(name="page", in="query", description="页码", @OA\Schema(type="int", default="1")),
     *   @OA\Parameter(name="limit", in="query", description="行数", @OA\Schema(type="int", default="10")),
     *   @OA\Response(response="200", description="The User")
     * )
     */
    public function articles()
    {
        $limit = $this->request->param('limit/d', 10);
        $data = [];
        return $this->success('success', $data);
    }
}
