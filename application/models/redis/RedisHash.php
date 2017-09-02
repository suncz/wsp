<?php

class RedisHash extends redisBase {

    public function __construct() {
        parent::__construct();
    }

    public function set($key, $id, $obj) {
        return static::$redis->hset($key, $id, $obj);
    }

    public function get($key, $id) {
        return static::$redis->hget($key, $id);
    }

    public function len($key) {
        return static::$redis->hLen($key);
    }

    public function contain($key, $id) {
        return static::$redis->hExsits($key, $id);
    }

    public function keys($key) {
        return static::$redis->hKeys($key);
    }

    public function values($key) {
        return static::$redis->hVals($key);
    }

    public function mget($key, $ids) {
        return static::$redis->hMGet($key, $ids);
    }

    public function mset($key, $obj) {
        return static::$redis->hMSet($key, $obj);
    }

    public function all($key) {
        return static::$redis->hGetAll($key);
    }

    public function delete($key, $id) {
        return static::$redis->hDel($key, $id);
    }

    /**
     * 增长指定字段的值
     * @param $key
     * @param $value
     * @return mixed
     */
    public function hIncrBy($key, $value) {
        return static::$redis->hIncrBy($key, $key, $value);
    }

    public function keysAll($regx) {
        return static::$redis->keys($regx);
    }

}
