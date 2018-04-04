<?php

class RedisMulti extends redisBase {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 执行多条redis命令
     * @param $key string
     */
    public function multi($key = \Redis::PIPELINE) {
        static::$redis->multi($key);
    }

    /**
     * 执行多条redis命令
     * redis exec method.
     * @return bool|string
     */
    public function exec() {
        return static::$redis->exec();
    }

}
