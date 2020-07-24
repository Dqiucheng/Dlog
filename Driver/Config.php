<?php
/**
 * Created by PhpStorm.
 * Author：Duan Hao Yang
 * User: Acer
 * Date: 2020/7/23
 * Time: 19:13
 */

//不可注释任何参数

return [
    'platform' => 'Tcc',    //使用平台，定义后不可更改
    'write_type' => 'WRITELOG',   //支持写入模式：WRITELOG，SEASLOG，REDISLOG（远程存储）
    'length' => 10,          //容器大小，适当设置可翻倍提升性能，REDISLOG最高30，SEASLOG最高20，LOG最高10,如多数情况单条日志过大，请适当减少
    'get_ip' => false,        //是否获取客户端IP
    'dlog_file' => '../',        //日志目录调整，由于各框架的性质不同，请根据实际情况调整，可调用DlogFile方法查看日志目录
    'is_timeline' => false,        //是否记录时间轴 true开启
    'realtime_write' => false,        //是否实时写入
    'redis_log' => [
        'prefix' => 'log:',     //前缀，不可更改
        'host' => '',           //默认
        'port' => '',           //默认
        'password' => '',       //
        'dbindex' => 0,         //默认 0

        'write_type' => 'SEASLOG',    //异常时被选方案
        'encoding' => 'MSGPACK',   //日志编码方式：JSON，MSGPACK，推荐选用MSGPACK
    ]
];