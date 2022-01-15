<?php
declare(strict_types=1);

namespace app\api\library;

// Server, 这样才能调用worker_server.php的 onWorkerStart，onWorkerReload，onConnect，onMessage，onClose，onError方法,根据需要重写方法~~
use think\worker\Server;
use GatewayWorker\Lib\Gateway;

class Worker extends Server
{
    protected $socket = 'http://0.0.0.0:2369';

    protected $client_id;

    public function onMessage($connection,$data)
    {
        // dump($connection->client_id);
        $connection->send($data);
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    public function onConnect($connection)
    {
        // 生成一个唯一用户的客户端id
        $this->client_id = time().rand(100,999);
        // 将生成的client_id赋给当前连接
        $connection->client_id = $this->client_id;

        //$this->worker->transport='ssl';
        // 在当前worker对象中新添属性  保存当前登录的用户的client_id 和 连接对象
        // 以便进行推送
        $this->worker->clientIdConnections[$connection->client_id] = $connection;

        // 将生成的client_id发送给客户端
        $json = [
            'type'  =>  'bind',
            'from'  =>  'worker',
            'to'    =>  $connection->client_id,
            'content' => $this->client_id
        ];
        // onConnect回调是在TCP建立连接后立刻被调用，如果在TCP建立连接后立刻在onConnect发送数据给客户端，会扰乱websocket握手，导致websocket握手失败
        $connection->onWebSocketConnect = function ($connection) use ($json) {
            $connection->send(json_encode($json));
        };
    }

}