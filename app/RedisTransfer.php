<?php
namespace app;
use think\cache\driver\Redis;
use think\facade\Config;

class RedisTransfer
{
    /**
     * 类型对应存入的缓存库
     * @var array
     */
    protected $dbType = [
        'base'=>0,
        'api'=>1,
    ];
    
    /**
     * 连接池
     *
     * @var string
     */
    protected $connection = "default";
    /**
     * 数据处理格式
     *
     * @var string
     */
    protected $format = "string";
    /**
     * redis对象
     *
     * @var [type]
     */
    protected $redis;

    protected $pipe;
    /**
     * 缓存类型
     * @var string $cacheType
     */
    protected $cacheType = 'base';
    /**
     * 缓存类型
     * @var string $cacheKeyName
     */
    protected $cacheKeyName = '';

    /**
     * 缓存构造函数
     * RedisTransfer constructor.
     * @param string $connection
     */
    public function __construct()
    {
        $connectionData = Config::get('cache.stores.redis');
        $this->redis = new Redis($connectionData);
        // $selDB = $this->dbType[$this->cacheType];
        // $this->redis->select($selDB);
    }

    public function init($cacheType)
    {
        $this->cacheType = $cacheType;
        $selDB = $this->dbType[$this->cacheType];
        $this->redis->select($selDB);
        $this->cacheKeyName = $cacheType;
    }

    /**
     * 缓存key名称
     *
     * @param string $strCacheKeyName
     * @return void
     */
    protected function getByCacheNames($strCacheKeyName = "")
    {
        return "{$this->cacheKeyName}:{$strCacheKeyName}";
    }
    /**
     * 获取缓存内容
     *
     * @param string $strCacheKeyName
     * @return void
     */
    public function getByCacheValue($cacheType = 'base', $strCacheKeyName = "")
    {
        $this->init($cacheType);
        $cacheKeyName = $this->getByCacheNames($strCacheKeyName);
        $cacheValue = $this->redis->get($cacheKeyName);
        if(!empty($cacheValue)){
            $cacheValue = $this->getValue($cacheValue);
        }
        return $cacheValue;
    }
    /**
     * 解析缓存内容
     *
     * @param string $strCacheValue
     * @return void
     */
    public function getValue($strCacheValue = "")
    {
        $cacheValue = $strCacheValue;
        switch($this->format){
            case 'json':
                $cacheValue = json_decode($strCacheValue,true);
            break;
            case 'gz':
                $cacheValue = gzuncompress($strCacheValue);
            break;
            case 'jsongz':
                $cacheValue = json_decode(gzuncompress($strCacheValue),true);
            break;
        }
        return $cacheValue ;
    }

    /**
     * 编译缓存值
     *
     * @param string $strCacheValue
     * @return void
     */
    public function setValue($strCacheValue = "")
    {
        $cacheValue = $strCacheValue;
        switch($this->format){
            case 'json':
                $cacheValue = json_encode($strCacheValue);
            break;
            case 'gz':
                $cacheValue = gzcompress($strCacheValue,6);
            break;
            case 'jsongz':
                $cacheValue = gzcompress(json_encode($strCacheValue),6);
            break;
        }
        return $cacheValue ;
    }

    /**
     * 设置缓存内容
     *
     * @param string $strCacheKeyName
     * @return void
     */
    public function setByCacheValue($cacheType = 'base', $strCacheKeyName = "",$strValue = "", $strTimeOut = 21600)
    {
        $this->init($cacheType);
        $cacheKeyName = $this->getByCacheNames($strCacheKeyName);
        $strValue = $this->setValue($strValue);
        return $this->redis->set($cacheKeyName,$strValue,$strTimeOut);
    }


    /**
     * 删除缓存
     *
     * @param string $strCacheKeyName
     * @return void
     */
    public function removeCacheValue($cacheType = 'base', $strCacheKeyName = "")
    {
        $this->init($cacheType);
        $cacheKeyName = $this->getByCacheNames($strCacheKeyName);
        dump($cacheKeyName);
        return $this->redis->del($cacheKeyName);
    }



}