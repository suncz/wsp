<?php

class RedisList extends redisBase {

    public function __construct() {
        parent::__construct();
    }

    public function lpush($key, $item) {
        return static::$redis->lPush($key, $item);
    }

    public function lpop($key) {
        return static::$redis->lPop($key);
    }

    function rpush($key, $item) {
        static::$redis->rPush($key, $item);
    }

    public function rpop($key) {
        return static::$redis->rPop($key);
    }

    public function brpop($key) {
        return static::$redis->brPop($key);
    }

    public function blpop($key) {
        return static::$redis->blPop($key);
    }

    public function get($key, $index) {
        return static::$redis->lIndex($key, $index);
    }

    public function set($key, $index, $item) {
        return static::$redis->lSet($key, $index, $item);
    }

    public function getRange($key, $start, $end) {
        return static::$redis->lRange($key, $start, $end);
    }

    public function remove($key, $start, $end) {
        return static::$redis->lTrim($key, $start, $end);
    }

    public function size($key) {
        return static::$redis->lSize($key);
    }

    /**
     * 移除list的指定值
     * @param mixed $value
     * @param int $count
     * @return mixed
     */
    public function lRem($key, $value, $count = 0) {
        return static::$redis->lRem($key, $value, $count);
    }

    public function expire($key, $ttl) {
        return static::$redis->expire($key, $ttl);
    }

    public function delete($key) {
        return static::$redis->delete($key);
    }

}
