<?php
declare (strict_types = 1);

namespace app\common\model;

use app\RedisTransfer;
use think\Model;

/**
 * @mixin \think\Model
 */
class User extends Base
{
    protected $table = 'users';

    protected $redis;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->redis = new RedisTransfer();
    }

    public function getUsers()
    {
        $lists = $this->redis->getByCacheValue('base', 'users');
        if (empty($lists)) {
            $lists = self::select();
            $this->redis->setByCacheValue('base', 'users', $lists);
        }
        return $lists;
    }

}
