<?php
include_once 'Dlog.php';
$log = new DLog\DLog('API');

$log->logs('测试' , ['aa' => 'aaaa', 'bbbb' => '内容']);
$log->logs('测试qq' , ['aa' => 'aaaa', 'bbbb' => '内容qq']);
$log->debug('啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊啊');
$log = new Dlog\Dlog('PAY');
var_dump($log->logs('aa' , ['aa' => 'aaaa', 'bbbb' => 'aa']));die;
//$log->logs('测试', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa');
//$log->logs('测试', 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa','',5);
for ($i = 0; $i < 5; $i++) {
   var_dump($log->logs('测试' . $i, ['aa' => 'aaaa', 'bbbb' => '内容' . $i]));
    $log->logs('测试' . $i, ['aa' => 'aaaa', 'bbbb' => '内容' . $i]);
}
//$log->Model = 'testsss2';
//$log->SetType('WRITELOG');
//$log->logs('测试测试', ['aa' => 'cccc', 'dddddddd' => '内容']);
//$log->Model = 'testsss3';
//$log->logs('测试测试', ['aa' => 'cccc', 'dddddddd' => '内容testsss3']);
