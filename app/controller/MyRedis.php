<?php
declare (strict_types = 1);

namespace app\controller;

use app\BaseController;
use app\common\model\User;
use think\App;

class MyRedis extends BaseController
{
    protected $redis;
    protected $setTime;

    public function __construct(App $app)
    {
        parent::__construct($app);
        $this->redis = new \Redis();
        $this->redis->connect('127.0.0.1', 6380);
        $this->setTime = 20;
    }

    // 加锁
    public function lock($key, $rant)
    {
        $ret = $this->redis->set($key, $rant, ['nx', 'ex' => $this->setTime]);
        return $ret;
    }

    // 解锁
    public function unlock($key, $param='')
    {
        if ($param != '') $this->redis->del($key);
    }

    // redis 缓存穿透，缓存击穿测试
    /*
     * 1. web 服务器启动时，提前将有可能被频繁并发访问的数据写入缓存，这样就能规避大量的请求，从而行程的排队阻塞
     * 2. 对所有可能查询的参数以hash形式存储，在控制层进行检验，不符合查询条件则丢弃，从而避免了对存储系统的查询压力
     * 3. 对缓存查询加锁，如果缓存不存在，就加锁，然后查询DB入缓存，最后解锁；其他进程如果发现有锁就等待；前提为已提前做好预知的缓存
     * 4. 不管数据库是否有数据，都在缓存中保存对应的key，值为空。避免数据库中没有该数据，导致频繁穿透缓存对数据库访问。
     */
    public function getUserData($user_id = 1)
    {
        $key = "user:{$user_id}";
        if ($this->redis->exists($key)) {
            $data = $this->redis->get($key);
            dump(json_decode($data, true));
            return show(200, "ok1", json_decode($data, true));
        }
        // 不存在，赋值
        // 生成锁的key
        $lock_key = $key . '_lock';

        // 锁定时长
        $rant = 5;
        $set_lock = $this->lock($lock_key, $rant);
        // 拿到互斥锁
        if ($set_lock) {
            $data = User::find($user_id)->toArray();
            $this->redis->set($key, json_encode($data), $this->setTime); // 自己设定时间
            $this->unlock($lock_key, $rant);
        } else {
            usleep(50000);
            $this->getUserData($user_id);
        }
        dump($data);
        return show(200, "ok2", $data);
    }


    public function getUserData2($user_id = 1)
    {
        $this->redis->del('lock');
        // 缓存存在则直接返回
        if ($this->redis->exists("user:{$user_id}")) {
            return $this->redis->get("user:{$user_id}");
        }
        // 防止当持有锁的进程崩溃互殴这删除锁失败时，其他进程无法获取锁
        $is_lock = $this->redis->setnx('lock', time() + 5);
        dump('is_lock: true,赋值成功，false,赋值不成功，lock已有值');
        dump($is_lock);
        if (!$is_lock) {
            $lock_time = $this->redis->get('lock');
            $now = time();
            dump('lock_time:' . $lock_time . ', time():' . $now);
            // 锁已过期，重置
            if ($lock_time < $now) {
                $this->unlock('lock');
                $this->redis->setnx('lock', time() + 5);
            }
        }
        dump('获取最新的lock: ');
        dump($this->redis->get('lock'));
        // 如果抢占失败再挂起50ms，直到缓存有数据
        $setLock = $this->redis->setnx('lock', 1);
        dump($setLock);
        if (!$setLock) {  // 如不存在，则赋值
            dump('123412');
            do {
                usleep(50000);  // 暂停50毫秒
            } while (!$this->redis->get("user:{$user_id}"));
            $data = $this->redis->get("user:{$user_id}");
        } else {
            $data = User::find($user_id);
            // 存入
            $this->redis->set("user:{$user_id}", $data);
            // 释放锁
            $this->redis->del('lock');
        }
        dump($data);
        // return $data;
    }


    // redis 数据队列
    public function setRedisList()
    {
        // 写入队列
        $orders = serialize(['user_id' => 1, 'goods_id' => 1]);
        $this->redis->lPush('orders', $orders);
        $orders = serialize(['user_id' => 1, 'goods_id' => 2]);
        $this->redis->lPush('orders', $orders);

        /*for ($i = 0; $i < 2; $i++) {
            sleep(1);
            dump($i);
        }*/

        // 读取队列
        $num = 0;
        while (true) {
            if ($this->redis->lLen('orders') == 0) {
                usleep(50000);
            } else {
                $orders = unserialize($this->redis->rPop('orders'));
                // dump($orders);
                // 写入数据库代码块
            }
            $num++;
            if ($num == 10) {
                break;
            }
        }
        dump('end');
    }


    // 接口redis 限流
    public function redisLimit($user_id = 0)
    {
        // 单个用户每分钟访问数
        $initNum = 100;
        $expire = 60;

        $key = $$user_id . '_minNum';
        $this->redis->watch($key);
        $limitVal = $this->redis->get($key);
        if ($limitVal) {
            $limitVal = json_decode($limitVal, true);
            $nowTime = time();
            // 计算当前时刻与上次访问的时间差 * 速率 = 可以补充的令牌个数
            $newNum = min($initNum, ($limitVal['num'] - 1) + (($initNum * $expire) * ($nowTime - $limitVal['time'])));
            if ($newNum > 0) {
                $redisVal = json_encode(['num' => $newNum, 'time' => time()]);
            } else {
                exit (json_encode(['status' => false, 'msg' => "当前时刻令牌消耗完"]));
            }
        } else {
            // 第一次访问时初始化令牌数量
            $redisVal = json_encode(['num' => $initNum, 'time' => time()]);
        }

        $this->redis->multi();
        $this->redis->set($key, $redisVal);
        $result = $this->redis->exec();

        if (!$result) {
            exit(json_encode(['status' => false, 'msg' => "访问次数过多！"]));
        }

        // To Do ...
    }
}
