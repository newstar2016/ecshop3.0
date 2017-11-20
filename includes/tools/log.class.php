<?php
/**
 * Created by PhpStorm.
 * User: gxgong
 * Date: 2017/11/17
 * Time: 11:13
 * Function: 日志工具类
 */
if(!defined("DIR_ROOT")){
    define("DIR_ROOT", dirname(dirname(dirname(__FILE__))));
}
define('LEVEL_FATAL', 0);  //致命的
define('LEVEL_ERROR', 1);  //错误
define('LEVEL_WARN', 2);   //警告
define('LEVEL_INFO', 3);   //信息
define('LEVEL_DEBUG', 4);  //调试
date_default_timezone_set('PRC');  //设置默认时区，中华人民共和国，避免受系统配置影响
/**
 * 记录操作过程中的日志信息
 * @version 1.0 20140725
 */
class Logger {
    static $LOG_LEVEL_NAMES = array(
        'FATAL', 'ERROR', 'WARN', 'INFO', 'DEBUG'
    );

    private $level = LEVEL_DEBUG;  //日志的级别
    private $rootDir = DIR_ROOT;

    static function getInstance() {
        return new Logger;
    }
    /**
     * 设置最小的log记录级别，小于该级别的log日志输出将被忽略掉
     * @param int $lvl -- 最小的log日志输出级别
     * @throws Exception
     */
    function setLogLevel($lvl) {
        if($lvl >= count(Logger::$LOG_LEVEL_NAMES)  || $lvl < 0) {
            throw new Exception('invalid log level:' . $lvl);
        }
        $this->level = $lvl;
    }
    //###################输出各个级别的日志信息---start==============
    function debug($message, $name = 'root',$logdir='') {
        $this->_log(LEVEL_DEBUG, $message, $name,$logdir);
    }
    function info($message, $name = 'root',$logdir='') {
        $this->_log(LEVEL_INFO, $message, $name,$logdir);
    }
    function warn($message, $name = 'root',$logdir='') {
        $this->_log(LEVEL_WARN, $message, $name,$logdir);
    }
    function error($message, $name = 'root',$logdir='') {
        $this->_log(LEVEL_ERROR, $message, $name,$logdir);
    }
    function fatal($message, $name = 'root',$logdir='') {
        $this->_log(LEVEL_FATAL, $message, $name,$logdir);
    }
    //###################输出各个级别的日志信息---end==============
    /**
     * 记录log日志信息
     * @param unknown_type $level
     * @param unknown_type $message
     * @param unknown_type $name
     * @param unknown_type $logdir  //存储日志的文件夹，统一在logs里面创建
     */
    private function _log($level, $message, $name,$logdir='') {
        if($level > $this->level) {
            return;
        }

        if($logdir==''){
            $log_file_path = $this->rootDir."/logs/".$name.'.log';
        }else{
            $logdirPath=$this->rootDir."/logs/{$logdir}";
            if(is_dir($logdirPath))
            {
                $log_file_path = $this->rootDir."/logs/{$logdir}/".$name.'.log';
            }
            else
            {
                echo "<script>alert('{$logdirPath}不存在，日志保存在{$name}.log中')</script>";
                $log_file_path = $this->rootDir."/logs/".$name.'.log';
            }

        }

        $log_level_name = Logger::$LOG_LEVEL_NAMES[$level];
        $content = date('Y-m-d H:i:s') . ' [' . $log_level_name . '] ' . $message .PHP_EOL;
        file_put_contents($log_file_path, $content, FILE_APPEND);
    }
}
$logger = Logger::getInstance();

//设置范例
//系统默认设置的是debug(4),即小于4的都会被输出记录

//$logger->setLogLevel(1);

//调用范例
//$logger->debug('this is my first debuglog', 'test');
//$logger->info('this is my first infolog', 'test');
//$logger->warn('this is my first warnlog', 'test');
//$logger->error('this is my first errorlog', 'test');
//$logger->fatal('this is my first fatallog', 'test');

//设置日志文件夹

