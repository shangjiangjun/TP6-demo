<?php
declare (strict_types = 1);

namespace app\common\model;

use app\RedisTransfer;
use think\Model;

/**
 * @mixin \think\Model
 */
class Notification extends Base
{
    protected $table = 'notifications';

    // protected $autoWriteTimestamp = true;

    // 设置json类型字段
    protected $json = ['awards'];

    // 设置JSON数据返回数组
    protected $jsonAssoc = true;

    protected $redis;

    public function __construct(array $data = [])
    {
        parent::__construct($data);
        $this->redis = new RedisTransfer();
    }

    public static function getUserNotify($uid)
    {
        $lists = self::where('uid', $uid)->select();
        return $lists;
    }

}
