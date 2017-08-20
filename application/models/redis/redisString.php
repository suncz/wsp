<?php

class redisString extends redisBase {

    public function __construct() {
        parent::__construct();
    }

    public function delete($key) {
        static::$redis->delete($key);
    }

    /**
     * 带过期时间的Set
     * @param $value
     * @param $time
     * @return mixed
     */
    public function setex($key, $value, $time) {
        return static::$redis->setex($key, $time, $value);
    }

    /**
     * Set
     * @param $value
     * @param $time
     * @return mixed
     */
    public function set($key, $value) {
        return static::$redis->set($key, $value);
    }

    /**
     * get
     * @param $value
     * @param $time
     * @return mixed
     */
    public function get($key) {
        return static::$redis->get($key);
    }

    /**
     * incr
     * @return mixed
     */
    public function incr($key) {
        return static::$redis->incr($key);
    }

    /**
     * decr
     * @return mixed
     */
    public function decr($key) {
        return static::$redis->decr($key);
    }

    /**
     * incrby
     * @param $num int 要增加的数额
     * @return mixed
     */
    public function incrBy($key, $num) {
        return static::$redis->incrBy($key, $num);
    }

    /**
     * exists
     * @return mixed
     */
    public function exists($key) {
        return static::$redis->exists($key);
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
