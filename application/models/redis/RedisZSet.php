<?php

/**
 * Class CRedisZSet
 */
class RedisZSet extends redisBase {

    public function __construct() {
        parent::__construct();
    }

    public function add($key, $score, $member) {
        static::$redis->zAdd($key, $score, $member);
    }

    public function remove($key, $member) {
        static::$redis->zDelete($key, $member);
    }

    public function getByOffset($key, $start, $end, $order = 'desc') {
        if ($order === 'desc') {
            return static::$redis->zRevRange($key, $start, $end);
        }

        return static::$redis->zRange($key, $start, $end);
    }

    public function getByScore($key, $start, $end, $order = 'desc') {
        if ($order == 'desc') {
            return static::$redis->zRevRangeByScore($key, $start, $end);
        } else {
            return static::$redis->zRangeByScore($key, $start, $end);
        }
    }

    public function index($key, $member) {
        return static::$redis->zRank($key, $member);
    }

    public function isMember($key, $member) {
        return static::$redis->zScore($key, $member) !== false;
    }

    public function score($key, $member) {
        return static::$redis->zScore($key, $member);
    }

    public function count($key, $start, $end) {
        return static::$redis->zCount($key, $start, $end);
    }

    public function size($key) {
        return static::$redis->zSize($key);
    }

    /**
     * @author  sunny
     * @return type
     */
    public function zcard($key) {
        return static::$redis->zcard($key);
    }

    /**
     * zincrBy
     * @return mixed
     */
    public function zincrBy($key, $member, $score = 1) {
        return static::$redis->zincrBy($key, $score, $member);
    }

    public function zRange($key, $star, $end, $withscores = null) {
        return static::$redis->zRange($key, $star, $end, $withscores);
    }

    public function zRevRange($key, $star, $end, $withscores = null) {
        return static::$redis->zRevRange($key, $star, $end, $withscores);
    }

    /**
     * 删除key
     * @return type
     * @author sunny
     */
    public function deleteKey($key) {
        return static::$redis->delete($key);
    }

    /**
     * 升序排名
     * @param type $member
     * @return type
     * @author 孙昌致<331942828@qq.com>
     */
    public function zRevRank($key, $member) {
        return static::$redis->zRevRank($key, $member);
    }

    /**
     * 降序排名
     * @param type $member
     * @return type
     * @author 孙昌致<331942828@qq.com>
     */
    public function zRank($key, $member) {
        return static::$redis->zRank($key, $member);
    }

}
