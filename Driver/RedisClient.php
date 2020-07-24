<?php
/**
 * Created by PhpStorm.
 * Author：Duan Hao Yang
 * User: Acer
 * Date: 2020/7/23
 * Time: 19:13
 */
namespace Dlog\Driver;


/**
 * redis类
 * @author dhy
 *
 */
class RedisClient extends \Redis
{
    public $host = '127.0.0.1';
    public $port = 6379;
    public $password = '';
    public $dbindex = 0; //选择数据库

    //链接redis
    public function rc()
    {
        try {
            $this->connect($this->host, $this->port,1);
            if ($this->password != '') {
                $this->auth($this->password);
            }
            if ($this->dbindex != 0) {
                $this->select($this->dbindex);
            }
        } catch (\Exception $e) {
            return null;
        }
        return $this;
    }

}