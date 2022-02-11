<?php
declare (strict_types = 1);

namespace app\api\controller\v1;

use app\BaseController;
use app\RedisTransfer;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT as JWTUtil;
use think\App;
use think\facade\Db;

class OAuth extends BaseController
{
    protected $redis;

    protected $client;

    protected $server;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->redis = new RedisTransfer();

        $dbName = env('DATABASE_DATABASE');
        $dbHost = env('DATABASE_HOSTNAME');

        $dsn = "mysql:dbname={$dbName};host={$dbHost}";
        $username = env('DATABASE_USERNAME');
        $password = env('DATABASE_PASSWORD');

        \OAuth2\Autoloader::register();

        // 创建存储的方式
        $storage = new \OAuth2\Storage\Pdo(['dsn' => $dsn, 'username' => $username, 'password' => $password]);

        // 创建server
        $server = new \OAuth2\Server($storage);

        // 添加password 授予类型
        $server->addGrantType(new \OAuth2\GrantType\UserCredentials($storage));

        // 添加refresh_token 授予类型
        $server->addGrantType(new \OAuth2\GrantType\RefreshToken($storage, [
            'always_issue_new_refresh_token'    => true,
        ]));

        $this->server = $server;

    }

    public function authorize()
    {
        $request = \OAuth2\Request::createFromGlobals();
        if ($this->user_client == 1) {
            $info = Db::table('oauth_clients')->field('client_id, client_secret')->where('id', $this->user_client)->find();
            $request->request['client_id'] = $info["client_id"];
            $request->request['client_secret'] = $info["client_secret"];
        }


        // 如果grant_type = password, 生成并获取 token
        // grant_type = refresh_token, 更新并获取 token
        $res = $this->server->handleTokenRequest($request)->send();
    }

    public function check()
    {
        $request = \OAuth2\Request::createFromGlobals();
        dump($request->headers);
        $request->headers['authorization'] = $request->headers['AUTHOR2'];
        /*if ($this->user_client == 1) {
            $info = Db::table('oauth_clients')->field('client_id, client_secret')->where('id', $this->user_client)->find();
            $request->request['client_id'] = $info["client_id"];
            $request->request['client_secret'] = $info["client_secret"];
        }*/
        if (!$this->server->verifyResourceRequest($request)) {
            $this->server->getResponse()->send();
            die;
        }

        // 获取用户信息
        $token = $this->server->getAccessTokenData($request);
        dump($token);
        // 执行业务
        echo "User ID associated with this token is {$token['user_id']}";

        // 存储用户信息

    }

    public function userLogin($username)
    {

    }

    public function getUser()
    {

    }


    // 2021-12-29 ----------------------------------------
    // 生成token
    public function createJwt($userId = 'zq')
    {
        $key = md5('zq8876!@!');        // jwt的签发密钥，验证token的时候需要用到
        $time = time();
        $expire = $time + 60 * 60 * 4;
        $token = [
            'user_id' => $userId,
            'iss'   => 'http://demo.tp6.com', // 签发组织
            'aud'   => 'xuyh',   // 签发作者
            'iat'   => $time,
            'nbf'   => $time,
            'exp'   => $expire
        ];
        $jwt = JWTUtil::encode($token, $key);
        return show(1,"OK",$jwt);
    }

    // 验证token, jwt 权限 api
    public function verifyJwt()
    {
        $param = $this->request->param();
        $jwt = arrayValue($param, '', 'jwt');
        $key = md5('zq8876!@!');
        try {
            $jwtAuth = json_encode(JWTUtil::decode($jwt, $key, array('HS256')));
            $authInfo = json_decode($jwtAuth, true);
            if (!empty($authInfo['user_id'])) {
                // $msg = ['status' => 1001, 'msg' => 'Token验证通过'];
                return show(1001,"Token验证通过");
            } else {
                return show(1002,"Token验证不通过，用户不存在");
            }
        } catch (ExpiredException $e) {
            return show(1003,"Token过期");
        } catch (\Exception $e) {
            return show(1004,"Token无效");
        }
    }
}
