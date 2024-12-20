<?php

namespace Dlog;

/**
 * Created by PhpStorm.
 * Author：Duan Hao Yang
 * User: Acer
 * Date: 2020/7/23
 * Time: 16:13
 */
include_once 'Base.php';

class Dlog extends Base
{

    /**
     * 经过测试，在单次请求中，写入条数在20之内的 性能REDISLOG与SEASLOG持平，
     * 并发情况下，本地写入模式可能会产生竞争锁，从而影响一定性能,适当设置容器长度可减少竞争锁频率（性能受容器长度影响）
     * @param $Model | 模块，决定了写入目录
     * @param $RedisObj | 如果有以存在的redis链接最好传一下，该链接必须与日志服务相匹配。
     */
    function __construct($Model = 'default', $RedisObj = null)
    {
        $this->Model = $Model;
        if (empty(self::$GroupId)) {
            $this->Run($RedisObj);
        }
    }


    /**
     * 记录日志信息
     * @access public
     * @param mixed $log_explain | 日志备注
     * @param mixed $msg | 日志信息
     * @param string $action | 记录的那个方法
     * @param string $type | 日志级别
     * @param array $bothway | RedisLog模式下参数为true时会开启双向写入
     * @param bool $lazy | false积极模式
     * @return $this
     */
    public function record($log_explain, $msg, $action = '', $type = 'info', $bothway = false, $lazy = true)
    {
        $this->EnLog([
            'action' => $action,
            'log_explain' => $log_explain,
            'msg' => $msg,
            'level' => $type,
        ]);

        if ($bothway && self::$Config['write_type'] == 'REDISLOG') {
            $logType = self::$Config['redis_log']['write_type'];
            self::$logType([end(static::$log[$this->Model])]);
        }

        if (!$this->lazy || !$lazy) {
            $this->save();
        }

        return true;
    }


    /**
     * 记录日志信息 record方法的别名
     * @return $this
     */
    public function Logs($log_explain, $msg, $action = '', $type = 'info', $bothway = false, $lazy = true)
    {
        return $this->record($log_explain, $msg, $action, $type, $bothway, $lazy);
    }


    /**
     * 实时写入日志信息
     * @access public
     * @param mixed $log_explain | 日志备注
     * @param mixed $msg | 日志信息
     * @param string $action | 记录的那个方法
     * @param string $type | 日志级别
     * @param array $bothway | RedisLog模式下参数为true时会开启双向写入
     * @return $this
     */
    public function write($log_explain, $msg, $action = '', $type = 'info', $bothway = false)
    {
        return $this->record($log_explain, $msg, $action, $type, $bothway, false);
    }


    /**
     * System is unusable.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function emergency($message)
    {
        return $this->log(__FUNCTION__, $message);
    }


    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function alert($message)
    {
        return $this->log(__FUNCTION__, $message);
    }


    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function critical($message)
    {
        return $this->log(__FUNCTION__, $message);
    }


    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function error($message)
    {
        return $this->log(__FUNCTION__, $message);
    }


    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function warning($message)
    {
        return $this->log(__FUNCTION__, $message);
    }


    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function notice($message)
    {
        return $this->log(__FUNCTION__, $message);
    }


    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function info($message)
    {
        return $this->log(__FUNCTION__, $message);
    }


    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function debug($message)
    {
        return $this->log(__FUNCTION__, $message);
    }


    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message)
    {
        return $this->record('', $message, '', $level);
    }


    /**
     * 获取日志信息
     * @return array
     */
    public function getLog($m = '')
    {
        return empty($m) ? static::$log : static::$log[$m];
    }


    /**
     * 获取日志写入的根目录
     * @return string
     */
    public function DlogFile()
    {
        return self::$Absolute;
    }


    /**
     * 设置懒惰模式：开启：true，关闭：false。【默认true】
     * @return string
     */
    public function SetLazy($Lazy)
    {
        $this->lazy = $Lazy;
        $this->Run(self::$Redis);
        return true;
    }


    /**
     * 动态配置容器长度
     * @return string
     */
    public function SetLen($length)
    {
        self::$Config['length'] = $length;
        return true;
    }


    /**
     * 获取配置信息
     * @return string
     */
    public function GetConfig($key = null)
    {
        if ($key == null) {
            $c = self::$Config;
        } else {
            $c = self::$Config[$key];
        }
        return $c;
    }


    /**
     * 动态配置写入模式：LOG，SEASLOG，REDISLOG【默认以配置文件为准】
     * @return string
     */
    public function SetType($type)
    {
        if (in_array($type, ['LOG', 'SEASLOG', 'REDISLOG'])) {
            self::$Config['write_type'] = $type;
            if ($type == 'REDISLOG') $this->registe(null);
            return true;
        }
        return false;
    }


    public function __call($method, $parameters)
    {
        return $this->log($method, $parameters);
    }

}
