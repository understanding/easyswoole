<?php

define('EASYSWOOLE_ROOT', realpath(getcwd()));

$file = EASYSWOOLE_ROOT.'/vendor/autoload.php';
if (file_exists($file)) {
    require $file;
}else{
    die('include composer autoload.php fail');
}


class Install
{
    public static function init()
    {
        \EasySwoole\EasySwoole\Core::getInstance();
        //强制更新easyswoole bin管理文件
        if(is_file(EASYSWOOLE_ROOT . '/easyswoole')){
            unlink(EASYSWOOLE_ROOT . '/easyswoole');
        }
        $path = '.'.str_replace(EASYSWOOLE_ROOT,'',__FILE__);
        file_put_contents(EASYSWOOLE_ROOT . '/easyswoole',"<?php require '{$path}';");
        self::releaseResource(__DIR__ . '/../src/Resource/Config.tpl', EASYSWOOLE_ROOT . '/Config.php');
        self::releaseResource(__DIR__ . '/../src/Resource/EasySwooleEvent.tpl', EASYSWOOLE_ROOT . '/EasySwooleEvent.php');
    }

    static function showTag($name, $value)
    {
        echo "\e[32m" . str_pad($name, 20, ' ', STR_PAD_RIGHT) . "\e[34m" . $value . "\e[0m\n";
    }

    public static function releaseResource($source, $destination)
    {
        clearstatcache();
        $replace = true;
        if (is_file($destination)) {
            $filename = basename($destination);
            echo "{$filename} has already existed, do you want to replace it? [ Y / N (default) ] : ";
            $answer = strtolower(trim(strtoupper(fgets(STDIN))));
            if (!in_array($answer, [ 'y', 'yes' ])) {
                $replace = false;
            }
        }
        if ($replace) {
            copy($source, $destination);
        }
    }

    public static function opCacheClear()
    {
        if (function_exists('apc_clear_cache')) {
            apc_clear_cache();
        }
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
    }


    public static function showLogo()
    {
        echo <<<LOGO
  ______                          _____                              _
 |  ____|                        / ____|                            | |
 | |__      __ _   ___   _   _  | (___   __      __   ___     ___   | |   ___
 |  __|    / _` | / __| | | | |  \___ \  \ \ /\ / /  / _ \   / _ \  | |  / _ \
 | |____  | (_| | \__ \ | |_| |  ____) |  \ V  V /  | (_) | | (_) | | | |  __/
 |______|  \__,_| |___/  \__, | |_____/    \_/\_/    \___/   \___/  |_|  \___|
                          __/ |
                         |___/

LOGO;
    }

    public static function showHelpForStart()
    {
        echo <<<HELP_START
\e[33m操作:\e[0m
\e[31m  easyswoole start\e[0m
\e[33m简介:\e[0m
\e[36m  执行本命令可以启动框架 可选的操作参数如下\e[0m
\e[33m参数:\e[0m
\e[32m  -d \e[0m                   以守护模式启动框架
HELP_START;
    }

    public static function showHelpForStop()
    {
        echo <<<HELP_STOP
\e[33m操作:\e[0m
\e[31m  easyswoole stop\e[0m
\e[33m简介:\e[0m
\e[36m  执行本命令可以停止框架 可选的操作参数如下\e[0m
\e[33m参数:\e[0m
\e[32m  -f \e[0m             强制停止服务
HELP_STOP;
    }

    public static function showHelpForRestart()
    {
        echo <<<HELP_RESTART
\e[33m操作:\e[0m
\e[31m  easyswoole restart\e[0m
\e[33m简介:\e[0m
\e[36m  停止并重新启动服务\e[0m
\e[33m参数:\e[0m
\e[32m  本操作没有相关的参数\e[0m\n
HELP_RESTART;
    }

    public static function showHelpForReload()
    {
        echo <<<HELP_RELOAD
\e[33m操作:\e[0m
\e[31m  easyswoole reload\e[0m
\e[33m简介:\e[0m
\e[36m  执行本命令可以重启所有Worker 可选的操作参数如下\e[0m
\e[33m参数:\e[0m
\e[32m  -a \e[0m           重启所有worker和task_worker进程
HELP_RELOAD;
    }

    public static function showHelp()
    {
        $version = \EasySwoole\EasySwoole\SysConst::VERSION;
        echo <<<DEFAULTHELP
\n欢迎使用为API而生的\e[32m easySwoole\e[0m 框架 当前版本: \e[34m{$version}\e[0m

\e[33m使用:\e[0m  easyswoole [操作] [选项]

\e[33m操作:\e[0m
\e[32m  install \e[0m      初始化easySwoole
\e[32m  start \e[0m        启动服务
\e[32m  stop \e[0m         停止服务
\e[32m  reload \e[0m       重载服务
\e[32m  restart \e[0m      重启服务
\e[32m  help \e[0m         查看命令的帮助信息\n
\e[31m有关某个操作的详细信息 请使用\e[0m help \e[31m命令查看 \e[0m
\e[31m如查看\e[0m start \e[31m操作的详细信息 请输入\e[0m easyswoole help --start\n\n
DEFAULTHELP;
    }
}

Install::showLogo();


$com = new \EasySwoole\Utility\CommandLine();

//设置命令回调
$com->setArgCallback($com::ARG_DEFAULT_CALLBACK,function ()use($com){
    if($com->getOptVal('start')){
        Install::showHelpForStart();
    }else if($com->getOptVal('stop')){
        Install::showHelpForStop();
    }else if($com->getOptVal('reload')){
        Install::showHelpForReload();
    }else if($com->getOptVal('restart')){
        Install::showHelpForRestart();
    }else{
        Install::showHelp();
    }
});

$com->setArgCallback('install',function (){
    Install::init();
    echo "install success\n";
});

$com->setArgCallback('start',function ()use($com){

    $conf = \EasySwoole\EasySwoole\Config::getInstance();
    if($com->getOptVal('d')){
        $conf->setConf("MAIN_SERVER.SETTING.daemonize", true);
    }
    Install::showTag('listen address', $conf->getConf('MAIN_SERVER.HOST'));
    Install::showTag('listen port', $conf->getConf('MAIN_SERVER.PORT'));
    Install::showTag('worker num', $conf->getConf('MAIN_SERVER.SETTING.worker_num'));
    Install::showTag('task worker num', $conf->getConf('MAIN_SERVER.SETTING.task_worker_num'));
    $user = $conf->getConf('MAIN_SERVER.SETTING.user');
    if(empty($user)){
        $user = get_current_user();
    }
    Install::showTag('run at user', $user);
    Install::showTag('daemonize', $conf->getConf("MAIN_SERVER.SETTING.daemonize"));
    Install::showTag('swoole version', phpversion('swoole'));
    Install::showTag('php version', phpversion());
    \EasySwoole\EasySwoole\Core::getInstance()->initialize();
    \EasySwoole\EasySwoole\Core::getInstance()->createServer()->start();
});

$com->setArgCallback('stop',function ()use($com){
    $force = $com->getOptVal('f');
    \EasySwoole\EasySwoole\Core::getInstance()->initialize();
    $Conf = \EasySwoole\EasySwoole\Config::getInstance();
    $pidFile = $Conf->getConf("MAIN_SERVER.SETTING.pid_file");
    if (file_exists($pidFile)) {
        $pid = file_get_contents($pidFile);
        if (!swoole_process::kill($pid, 0)) {
            echo "PID :{$pid} not exist \n";
            return false;
        }
        if ($force) {
            swoole_process::kill($pid, SIGKILL);
        } else {
            swoole_process::kill($pid);
        }
        //等待5秒
        $time = time();
        $flag = false;
        while (true) {
            usleep(1000);
            if (!swoole_process::kill($pid, 0)) {
                echo "server stop at " . date("y-m-d h:i:s") . "\n";
                if (is_file($pidFile)) {
                    unlink($pidFile);
                }
                $flag = true;
                break;
            } else {
                if (time() - $time > 5) {
                    echo "stop server fail.try -f again \n";
                    break;
                }
            }
        }
        return $flag;
    } else {
        echo "PID file does not exist, please check whether to run in the daemon mode!\n";
        return false;
    }
});

$com->setArgCallback('reload',function ()use($com){
    $all = $com->getOptVal('a');
    \EasySwoole\EasySwoole\Core::getInstance()->initialize();
    $Conf = \EasySwoole\EasySwoole\Config::getInstance();
    $pidFile = $Conf->getConf("MAIN_SERVER.SETTING.pid_file");
    if (file_exists($pidFile)) {
        if (!$all) {
            $sig = SIGUSR2;
            Install::showTag('reloadType',"only-task");
        } else {
            $sig = SIGUSR1;
            Install::showTag('reloadType',"all-worker");
        }

        Install::opCacheClear();
        $pid = file_get_contents($pidFile);
        if (!swoole_process::kill($pid, 0)) {
            echo "pid :{$pid} not exist \n";
            return;
        }
        swoole_process::kill($pid, $sig);
        echo "send server reload command at " . date("y-m-d h:i:s") . "\n";
    } else {
        echo "PID file does not exist, please check whether to run in the daemon mode!\n";
    }
});



$com->parseArgs($argv);


