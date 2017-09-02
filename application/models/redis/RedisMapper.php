<?php

class RedisMapper extends redisBase {

    public function __construct() {
        parent::__construct();
    }

    public function set($key, $id, $obj) {
        $key = $key . '.' . $id;
        $value = json_encode($obj);
        return static::$redis->set($key, $value);
    }

    public function get($key, $id) {
        $key = $key . '.' . $id;
        $value = static::$redis->get($key);
        $value = json_decode($value);
        return $value;
    }

    public function mget($key, $ids) {
        $keys = [];
        foreach ($ids as $id) {
            $keys[$key . '.' . $id] = $id;
        }
        $values = static::$redis->getMultiple(array_keys($keys));
        $objs = [];
        foreach ($values as $key => $value) {
            if ($value != false) {
                $objs[$ids[$key]] = json_decode($value);
            }
        }
        return $objs;
    }

    public function delete($key, $id) {
        $key = $key . '.' . $id;
        return static::$redis->delete($key);
    }

}
