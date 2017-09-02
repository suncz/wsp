<?php

class RedisSet extends redisBase {

    public function __construct() {
        parent::__construct();
    }

    public function delete($key) {
        return static::$redis->delete($key);
    }

    public function sadd($key, $value) {
        return static::$redis->sadd($key, $value);
    }

    /**
     * 集合添加多个值
     * @param $value array
     * @return mixed
     */
    public function sAddArray($key, array $value) {
        return static::$redis->sAddArray($key, $value);
    }

    public function sismember($key, $value) {
        return static::$redis->sismember($key, $value);
    }

    public function srem($key, $value) {
        return static::$redis->srem($key, $value);
    }

    /**
     * 查询集合内所有元素
     * @return mixed
     */
    public function smembers($key) {
        return static::$redis->smembers($key);
    }

    /**
     * 统计set的元素的个数
     * @return int
     */
    public function scard($key) {
        return static::$redis->scard($key);
    }

    /**
     * 统计set的元素的个数
     * @return int
     */
    public function scardByKey($key) {
        return static::$redis->scard($key);
    }

    /**
     * 返回一个随机元素
     * @return mixed
     */
    public function sRandmember($key) {
        return static::$redis->sRandMember($key);
    }

    /**
     * 检查key是否存在
     * @return type
     */
    public function exists($key) {
        return static::$redis->exists($key);
    }

    public function sscan($key, $count, $cursor = null, $pattern = '') {
        $arr = [];
        $arr['data'] = static::$redis->sScan($key, $cursor, $pattern, $count);
        $arr['cursor'] = $cursor;
        return $arr;
    }

    /**
     * expire
     * @param $ttl int 生存时间
     * @return mixed
     */
    public function expire($key, $ttl) {
        return static::$redis->expire($key, $ttl);
    }

}
