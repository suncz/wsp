<?php
require_once 'redisKey.php';
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2017, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2017, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 3.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Redis Caching Class
 *
 * @package	   CodeIgniter
 * @subpackage Libraries
 * @category   Core
 * @author	   Anton Lindqvist <anton@qvister.se>
 * @link
 */
class redisBase {

    /**
     * Default config
     *
     * @static
     * @var	array
     */
    protected static $_default_config = array(
        'socket_type' => 'tcp',
        'host' => '127.0.0.1',
        'password' => NULL,
        'port' => 6379,
        'timeout' => 0
    );

    /**
     * Redis connection
     *
     * @var	Redis
     */
    static protected $redis;

    /**
     * An internal cache for storing keys of serialized values.
     *
     * @var	array
     */
    protected $_serialized = array();

    // ------------------------------------------------------------------------

    /**
     * Class constructor
     *
     * Setup Redis
     *
     * Loads Redis config file if present. Will halt execution
     * if a Redis connection can't be established.
     *
     * @return	void
     * @see		Redis::connect()
     */
    public function __construct() {
        if (self::$redis instanceof \Redis) {
            return;
        }
        if (!$this->is_supported()) {
            log_message('error', 'Cache: Failed to create Redis object; extension not loaded?');
            return;
        }

        $CI = & get_instance();

        if ($CI->config->load('redis', TRUE, TRUE)) {
            $config = array_merge(self::$_default_config, $CI->config->item('redis'));
        } else {
            $config = self::$_default_config;
        }

        self::$redis = new \Redis();

        try {
            if ($config['socket_type'] === 'unix') {
                $success = self::$redis->connect($config['socket']);
            } else { // tcp socket
                $success = self::$redis->connect($config['host'], $config['port'], $config['timeout']);
            }

            if (!$success) {
                log_message('error', 'Cache: Redis connection failed. Check your configuration.');
            }

            if (isset($config['password']) && !self::$redis->auth($config['password'])) {
                log_message('error', 'Cache: Redis authentication failed.');
            }
        } catch (RedisException $e) {
            log_message('error', 'Cache: Redis connection refused (' . $e->getMessage() . ')');
        }

        // Initialize the index of serialized values.
        $serialized = self::$redis->sMembers('_ci_redis_serialized');
        empty($serialized) OR $this->_serialized = array_flip($serialized);
    }

    // ------------------------------------------------------------------------



    // ------------------------------------------------------------------------

    /**
     * Save cache
     *
     * @param	string	$id	Cache ID
     * @param	mixed	$data	Data to save
     * @param	int	$ttl	Time to live in seconds
     * @param	bool	$raw	Whether to store the raw value (unused)
     * @return	bool	TRUE on success, FALSE on failure
     */
    public function save($id, $data, $ttl = 60, $raw = FALSE) {
        if (is_array($data) OR is_object($data)) {
            if (!self::$redis->sIsMember('_ci_redis_serialized', $id) && !self::$redis->sAdd('_ci_redis_serialized', $id)) {
                return FALSE;
            }

            isset($this->_serialized[$id]) OR $this->_serialized[$id] = TRUE;
            $data = serialize($data);
        } elseif (isset($this->_serialized[$id])) {
            $this->_serialized[$id] = NULL;
            self::$redis->sRemove('_ci_redis_serialized', $id);
        }

        return self::$redis->set($id, $data, $ttl);
    }

    // ------------------------------------------------------------------------



    // ------------------------------------------------------------------------

 

    // ------------------------------------------------------------------------

    /**
     * Get cache metadata
     *
     * @param	string	$key	Cache key
     * @return	array
     */
    public function get_metadata($key) {
        $value = $this->get($key);

        if ($value !== FALSE) {
            return array(
                'expire' => time() + self::$redis->ttl($key),
                'data' => $value
            );
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Check if Redis driver is supported
     *
     * @return	bool
     */
    public function is_supported() {
        return extension_loaded('redis');
    }

    // ------------------------------------------------------------------------

    /**
     * Class destructor
     *
     * Closes the connection to Redis if present.
     *
     * @return	void
     */
    public function __destruct() {
        if (self::$redis) {
            self::$redis->close();
        }
    }

}
