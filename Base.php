<?php

namespace Dlog;

/**
 * Created by PhpStorm.
 * Author：Duan Hao Yang
 * User: Acer
 * Date: 2020/7/23
 * Time: 18:54
 */
class Base
{
    public $lazy = true;

    /**
     * 日志信息
     * @var array
     */
    protected static $log = [];


    protected static $Redis;
    protected static $Config;
    protected static $GroupId;
    protected static $Absolute;
    public $Model;


    protected function Run($RedisObj)
    {
        require_once('Driver/Common.php');
        self::$GroupId = uniqid();
        if (empty(self::$Config['platform'])) {
            self::$Config = LogConfig('Config.php');
        }
        if (self::$Config['realtime_write']) $this->lazy = false;
        $this->registe($RedisObj);
        if ($this->lazy) {
            $this->shutdown();
        }
        self::$Absolute = getcwd() . DIRECTORY_SEPARATOR . self::$Config['dlog_file'];
    }


    protected function registe($RedisObj)
    {
        if (self::$Config['write_type'] == 'REDISLOG') {
            if (is_object($RedisObj)) {
                self::$Redis = $RedisObj;
            } else {
                require_once('Driver/RedisClient.php');
                self::$Redis = new Driver\RedisClient();
                if (!empty(self::$Config['redis_log']['host'])) {
                    self::$Redis->host = self::$Config['redis_log']['host'];
                    self::$Redis->port = self::$Config['redis_log']['port'];
                    self::$Redis->password = self::$Config['redis_log']['password'];
                    self::$Redis->dbindex = self::$Config['redis_log']['dbindex'];
                }
                self::$Redis = self::$Redis->rc();
                if (self::$Redis == NULL) {
                    self::$Config['write_type'] = self::$Config['redis_log']['write_type'];
                }
            }
        }
    }


    private function shutdown()
    {
        $CLASS = __CLASS__;
        register_shutdown_function([new $CLASS, 'endSave']);
    }


    protected function EnLog($data)
    {
        static $log_sort = 0;
        static $length = 0;
        if ($length == self::$Config['length']) {
            $this->save();
            $length = 0;
        }
        $data['groupid'] = self::$GroupId;
        $data['model'] = $this->Model;
        if (empty($data['action'])) {
            $data['action'] = $_SERVER['REQUEST_URI'];
        }
        if (self::$Config['get_ip']) {
            $data['action'] = 'client：' . Ip() . " | " . $data['action'];
        }
        if (self::$Config['is_timeline']) {
            $data['RunTime'] = number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 6);
        }

        $data['groupid'] = $data['groupid'] . '_' . $log_sort;
        $data['date'] = date('Y-m-d H:i:s');
        $log_sort++;
        $length++;
        static::$log[$data['model']][] = $data;
        return true;
    }


    /**
     * 保存日志
     * @return bool
     */
    public function endSave()
    {
        if (!empty(self::$Config['show_error_msg'])) {
            $last_err = error_get_last();
            if ($last_err) {
                $this->Model = "error";
                $this->EnLog([
                    'action' => '',
                    'log_explain' => '',
                    'msg' => $last_err['message'] . "[ file：{$last_err['file']} ]",
                    'level' => 'error',
                ]);
            }
        }


        end(static::$log);
        $key = key(static::$log);
        $endkey = count(static::$log[$key]) - 1;
        static::$log[$key][$endkey]['end'] = '1';
        if (!self::$Config['is_timeline'])
            static::$log[$key][$endkey]['RunTime'] = number_format(microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'], 6);
        return $this->save();
    }


    /**
     * 保存日志
     * @return bool
     */
    public function save()
    {
        $logType = self::$Config['write_type'];
        return $this->$logType();
    }


    protected function WRITELOG($data = [])
    {
        $data = empty($data) ? static::$log : $data;
        $Ym = date("Ym");
        $Ymd = date("Ymd");
        $dirs = self::$Absolute . "logs" . DIRECTORY_SEPARATOR;
        foreach ($data as $k => $v) {
            $dir = $dirs . "{$k}" . DIRECTORY_SEPARATOR . $Ym;
            if (!is_dir($dir)) {
                if (!mkdir($dir, 0755, true)) {
                    continue;
                }
            }
            $content = '';
            foreach ($v as $kk => $vv) {
                $content .= $vv['date'] . ' | ' . $vv['level'] . ' | ';
                $content .= $vv['groupid'] . ' | ' . $vv['action'] . "\r";
                if (!empty($vv['RunTime'])) $content .= "[ RunTime:{$vv['RunTime']}s ] ";
                $content .= $vv['log_explain'] . "-> ";
                if (is_array($vv['msg'])) {
                    $content .= json_encode($vv['msg'], JSON_UNESCAPED_UNICODE) . "\n\n";
                } else {
                    $content .= $vv['msg'] . "\n\n";
                }
            }
            $fp = fopen($dir . DIRECTORY_SEPARATOR . "{$Ymd}.log", 'a');
            flock($fp, LOCK_EX);
            fwrite($fp, $content);
            flock($fp, LOCK_UN);
            fclose($fp);
        }
        static::$log = [];
        return true;
    }


    protected function SEASLOG($data = [])
    {
        try {
            $data = empty($data) ? static::$log : $data;
            \Seaslog::setBasePath(self::$Absolute . 'logs');   //跟目录
            $Ym = DIRECTORY_SEPARATOR . date("Ym");
            foreach ($data as $k => $arr) {
                \SeasLog::setLogger($k . $Ym);   //子目录
                foreach ($arr as $k => $v) {
                    \Seaslog::setRequestID($v['groupid']);
                    $str = $v['action'] . "\r";
                    if (!empty($v['RunTime'])) $str .= "[ RunTime:{$v['RunTime']}s ] ";
                    $str .= $v['log_explain'] . "-> ";
                    if (is_array($v['msg'])) {
                        $str .= json_encode($v['msg'], JSON_UNESCAPED_UNICODE) . "\n";
                    } else {
                        $str .= $v['msg'] . "\n";
                    }
                    \SeasLog::log($v['level'], $str);
                }
            }
            static::$log = [];
            return true;
        } catch (\Exception $e) {
            return $this->LOG();
        }
    }


    protected function REDISLOG()
    {
        try {
            $log[] = self::$Config['redis_log']['prefix'] . self::$Config['platform'];
            switch (self::$Config['redis_log']['encoding']) {
                case 'JSON':
                    foreach (static::$log as $arr) {
                        $log[] = json_encode($arr);
                    }
                    break;
                case 'MSGPACK':
                    foreach (static::$log as $arr) {
                        $log[] = msgpack_pack($arr);
                    }
                    break;
                default:
                    throw new \Exception('未知编码格式');
                    break;
            }
            call_user_func_array([self::$Redis, 'lPush'], $log);
            static::$log = [];
            return true;
        } catch (\Exception $e) {
            unset($log);
            $logType = self::$Config['redis_log']['write_type'];
            return $this->$logType();
        }
    }

}