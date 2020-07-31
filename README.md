SDK Dlog 2.0
===============

Dlog2.0相对一1.0版本在性能上有了一定的提升，同时增加了一些新的特性，建议升级。

## 主要新特性

* 支持thinkphp5以上日志驱动切换
* 新增大量轻巧函数使用
* 新增自动记录错误信息功能
* TpDlog驱动采用`PHP7`相关语法
* 支持更多的`PSR`规范
* 规范日志格式
* 规范函数及变量命名规则
* 统一和精简大量用法
* 性能大幅优化
* 修复已知BUG


> Dlog2.0的运行环境默认要求5.5以上版本，TpDlog驱动下要求7.1以上版本。

## 安装，以ThinkPHP6为例
将下载好的SDk放在extend目录即可


## 简单使用
~~~

use \Dlog\Dlog;

$Dlog new DLog('test'); //实例化时的参数决定了日志目录结构

$Dlog->record('日志备注'，'日志内容');

$Dlog->DlogFile();  //查看日志写入根目录
    
~~~

## ThinkPHP6日志驱动切换
修改项目config目录下的log.php文件，在channels数组下增加以下配置项即可
~~~

// 其它日志通道配置
'dlog' => [
    // 日志记录方式
    'type'           => '\\Dlog\\TpDlog',
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
],
        
~~~
配置完成后所有产生的日志将交给Dlog来代理。
> 如需全权将底层交由Dlog来托管在vendor/topthink/framework/src/think/log/Channel.php文件相应位置增加以下代码即可

~~~

if (is_string($msg) && !empty($context)) {
    $replace = [];
    foreach ($context as $key => $val) {
        $replace['{' . $key . '}'] = $val;
    }

    $msg = strtr($msg, $replace);
}

//下面是新增的代码，大约在90行的位置
if ($this->logger instanceof \Dlog\TpDlog) {
    $this->logger::$Model = $type;
    return $this->logger->record('', $msg, '', $type, false, $lazy);
}
    
~~~


## 注意事项

* 如需使用REDISLOG请联系作者提供DlogServer端
* REDISLOG需要Redis服务支持
* Redis服务不建议与Dlog客户端部署在同一台机器上
* DlogServer端分两个版本，PHP版本与Golang版本
* DlogServer端不建议与Dlog客户端部署在同一台机器上


## 版权信息

Dlog2.0遵循Apache2开源协议发布，并提供免费使用。

本项目包含的第三方源码和二进制文件之版权信息另行标注。

All rights reserved。

Author：DHY

CSDN：秋丞

个人开发希望大家多多支持，积极反馈BUG或优化建议。
