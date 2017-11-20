<?php
/**
 * Created by PhpStorm.
 * User: gxgong
 * Date: 2017/11/17
 * Time: 11:17
 * Funtion: 测试用
 */
define('IN_ECS', true);
require(dirname(dirname(__FILE__)) . '/includes/init.php');
//日志调用方式
$logger->info('this is my first infolog','test','pay');
//$logger->warn('this is my first warnlog', 'test');
//$logger->error('this is my first errorlog', 'test');
//$logger->fatal('this is my first fatallog', 'test');

//echo $_SERVER['DOCUMENT_ROOT'];
//define('BASE_PATH',str_replace('\\','/',realpath(dirname(__FILE__).'/'))."/");
//
//echo BASE_PATH;