<?php

namespace Dlog;

/**
 * Created by PhpStorm.
 * Author：Duan Hao Yang
 * User: Acer
 * Date: 2020/7/23
 * Time: 16:13
 */

use think\contract\LogHandlerInterface;

class TpDlog extends Dlog implements LogHandlerInterface
{

    /**
     * 经过测试，在单次请求中，写入条数在20之内的 性能REDISLOG与SEASLOG持平，
     * 并发情况下，本地写入模式可能会产生竞争锁，从而影响一定性能,适当设置容器长度可减少竞争锁频率（性能受容器长度影响）
     * @param $Model | 模块，决定了写入目录
     * @param $RedisObj | 如果有以存在的redis链接最好传一下，该链接必须与日志服务相匹配。
     */
    function __construct($c = '', $RedisObj = null)
    {
        static::$Config = $c;
        if (empty(self::$GroupId)) {
            $this->Run($RedisObj);
        }
    }


    /**
     * 保存日志
     * @return bool
     */
    public function save(array $log = []): bool
    {
        if (!empty($log)) {
            foreach ($log as $k => $arr) {
                static::$Model = $k;
                foreach ($arr as $kk => $vv) {
                    $this->EnLog([
                        'log_explain' => '',
                        'msg' => $vv,
                        'level' => $k,
                    ]);
                }
            }
        }
        $logType = self::$Config['type'];
        return $this->$logType();
    }

}
