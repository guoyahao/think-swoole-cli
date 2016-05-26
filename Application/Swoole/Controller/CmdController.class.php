<?php
// +----------------------------------------------------------------------
// | Swoole [ swoole - task ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.swoole.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 郭亚浩 <515449117@qq.com> <http://www.guoyahao.com>
// +----------------------------------------------------------------------

namespace Swoole\Controller;
use Think\Controller;

/**
 * 命令类
 */
class CmdController extends IndexController
{
	/**
	 *  入口判断是否是CLI访问
	 */
	public function __construct()
	{
		parent::_initialize();
		
		define('SWOOLE_TASK_PID_PATH', MODULE_PATH.'Tmp'.DIRECTORY_SEPARATOR.'swoole-task.pid');
		
		// 检查系统及php设置
		$this->CheckSystermCmd();
				
		// 如果没有请求类型就提示错误
		if($this->opt->t == 'server')
		{
			$this->CheckServerCmd();
		}
		elseif($this->opt->t == 'client')
		{
			$this->CheckClientCmd();
		}
		else
		{
			$this->strcolor("请选择运行方式：[ -t ] server/client", "red", null , 1);
		}
	}

	/**
	 *  入口文件
	 */
	public function index(){}
	
	/**
	 *  检查系统所需要的配置以及相关函数
	 */
	private function CheckSystermCmd()
	{
		$set = file_get_contents(MODULE_PATH.DIRECTORY_SEPARATOR.'Conf/php-unix.set');
		
		if($set=='ok') return true;
		
		$this->strcolor("开始启动配置：","green",null);
		
		/**
		 * 默认时区定义
		 */
		date_default_timezone_set('Asia/Shanghai');
		
		$this->strcolor("  [ Setting ]设定时区：Asia/Shanghai","cyan",null);
		
		/**
		 * 设置错误报告模式
		 */
		error_reporting(E_ALL);
		
		$this->strcolor("  [ Setting ]设置错误报告模式：E_ALL","cyan",null);
		
		/**
		 * 设置默认区域
		 */
		setlocale(LC_ALL,"zh_CN.utf-8");
		
		$this->strcolor("  [ Setting ]设置默认区域：zh_CN.utf-8","cyan",null);
		
		/**
		 * 检查exec 函数是否启用
		 */
		if(!function_exists('exec'))
		{
			$this->strcolor('  [ Error ]PHP 函数：exec function is disabled'.PHP_EOL,'red',null,1);
		}
		else
		{
			$this->strcolor("  [ Check ]PHP 函数：exec function is [ ok ]","cyan",null);
		}
		
		/**
		 * 检查命令 lsof 命令是否存在
		 */
		exec("whereis lsof",$out);
		
		if($out[0]=='lsof:')
		{
			$this->strcolor('  [ Error ]lsof is not found'.PHP_EOL,'red',null,1);
		}
		else
		{
			$this->strcolor("  [ Check ]unix 函数：lsof cmd is [ ok ]", "cyan", null);
		}
		
		$this->writeLog('ok',MODULE_PATH.DIRECTORY_SEPARATOR.'Conf/php-unix.set',true);
	}
	
	/**
	 *  检查Server配置以及执行命令
	 */
	private function CheckServerCmd()
	{		
		if(empty($this->opt->e))
		{
			$this->strcolor('检查Server配置：','green',null);
			
			$this->strcolor("  [ Error ] 请输入Server端的命令参数：[ -e ] 'start','stop','restart','list','help' ", "red" , null , 1);
		}
		
		$this->strcolor('检查Server配置：','green',null);
		
		$this->strcolor("  [ Setting ] 指定此参数 [ -e ] 命令为: {$this->opt->e}", "cyan" ,null);
		
		if(!in_array($this->opt->e, array('stop','list','help')))
		{
			if($this->opt->d)
			{
				$this->strcolor("  [ Setting ] 指定此参数 [ -d ] 以守护进程模式运行", "cyan" ,null);
			}
			else
			{
				$this->strcolor("  [ Setting ] 指定此参数 [ -d ] 以非守护进程模式运行", "cyan" ,null);
			}
			
			$this->strcolor("  [ Setting ] 指定监听ip [ -h ] 设置为: {$this->opt->h}", "cyan" ,null);
			
			$this->strcolor("  [ Setting ] 指定监听端口port [ -p ] 设置为: {$this->opt->p}", "cyan" ,null);
			
			$this->strcolor("  [ Setting ] 指定服务进程名称 [ -n ] 设置为: {$this->opt->n}", "cyan" ,null);
			
		}
		
		try
		{
			$this->strcolor("执行Server命令：{$this->opt->e}",'green',null);
			
			$serverCmd = "server{$this->opt->e}";
				
			return self::$serverCmd();			
		}
		catch (\Exception $e)
		{
			$this->strcolor("  [ Error ] 输入SwooleServer端的命令参数 -e {$this->opt->e} 不存在",'red',null,1);
		}
	}
		
	/**
	 *  Server端操作说明
	 */
	public function serverhelp()
	{
		$help = <<<HELP
命令:
       [ -t | -e | -d | -h | -p | -n ]		
  参数:		
      -t 选择参数值：server/client
			
      -e 可执行命令
	
                   启动-e start 启动SwooleServer服务	
	
                   关闭-e stop  停止SwooleServer服务 [ 必须指定端口 ]	
	
                   重启-e restart 重启SwooleServer服务			
	
                   获取-e list  列出SwooleServer服务进程
	
      -d 指定此参数,以守护进程模式运行  (1:守护 0:不守护 )例如: php swoole.php cmd -t server -e start -h 127.0.0.1 -d 1 默认: 1 
		
      -h 指定监听ip,例如 php swoole.php cmd -t server -e start -h 127.0.0.1 默认：0.0.0.0
		
      -p 指定监听端口port， 例如 php swoole.php cmd -t server -e start -h 127.0.0.1 -p 9510
		
      -n 指定服务进程名称，例如 php swoole.php cmd -t server -e start -n test, 则进程名称为SwooleServer-test	
----------------------------------------------------------  ------------------------------------------------------------\n
HELP;
		
		$this->strcolor("  {$help}",'green',null,1);
	}
	
	
	/**
	 * 启动SwooleServer服务
	 * @param string $host IP地址
	 * @param int $port 端口
	 * @param int $daemon 是否守护进行
	 * @param string $name
	 */
	public function serverstart()
	{
		$this->strcolor("  [ Waiting... ] 正在启动 SwooleServer服务",'cyan',null); 
		
		if(file_exists(SWOOLE_TASK_PID_PATH))
		{
			$pid = explode("\n",file_get_contents(SWOOLE_TASK_PID_PATH));
			
			$cmd="ps ax | awk '{ print $1 }' | grep -e \"^{$pid[0]}$\"";
			
			exec($cmd,$out);

			if(!empty($out))
			{
				$this->strcolor("  [ Warning ] swoole-task.pid文件 ".SWOOLE_TASK_PID_PATH." 存在，swoole-server服务器已经启动，进程pid为:{$pid[0]}",'red',null,1);
			}
			else
			{
				$this->strcolor("  [ Warning ] swoole-task pid文件 ".SWOOLE_TASK_PID_PATH." 存在，可能SwooleServer服务上次异常退出(非守护模式ctrl+c终止造成是最大可能)",'red',null,1);
				
				unlink(SWOOLE_TASK_PID_PATH);
			}
		}
		
		$bind = $this->portBind($this->opt-p);
		
		if($bind)
		{
			foreach($bind as $k=>$v)
			{
				if($v['ip']=='*'||$v['ip']==$this->opt->h)
				{
					$this->strcolor("  [ Error ] 端口已经被占用 {$this->opt->h}:$this->opt->p, 占用端口进程ID {$k}",'red',null,1);
				}
			}
		}
		
		// 守护进程模式
		if($this->opt->d)
		{
			$startcmd="php swoole.php Server/ServRun/host/{$this->opt->h}/port/{$this->opt->p}/daemon/{$this->opt->d}/processName/{$this->opt->n}";
				
			exec($startcmd,$outstart);
			
			if(empty($outstart))
			{
				$this->strcolor("  [ Success ] 启动 SwooleServer服务成功",'green',null,1);
			}
			else
			{
				$this->strcolor("  [ Error ] 启动 SwooleServer服务失败",'red',null,1);
			}
		}
		// 非守护模式
		else 
		{
			$server = new ServerController($this->opt->h,$this->opt->p,$this->opt->d,$this->opt->n);
			
			$server->ServRun();
		}
	}
	
	/**
	 * 停止SwooleServer服务
	 * @param string $host
	 * @param int $port
	 * @param boolean $isRestart
	 */
	public function serverstop($isRestart=false)
	{
		$this->strcolor("  [ Waitting ]正在停止SwooleServer服务","cyan",null);
	
		if(!file_exists(SWOOLE_TASK_PID_PATH))
		{
			$this->strcolor("  [ Error ]swoole-task.pid文件:".SWOOLE_TASK_PID_PATH."不存在","red" , null ,1);
		}
		
		$pid = explode("\n",file_get_contents(SWOOLE_TASK_PID_PATH));
		
		$bind = $this->portBind($this->opt->p);
		
		if(empty($bind)||!isset($bind[$pid[0]]))
		{
			$this->strcolor("  [ Error ] 指定端口占用进程不存在 port:{$this->opt->p}, pid:{$pid[0]}","red" , null ,1);
		}
		
		$cmd="kill {$pid[0]}";
		
		exec($cmd);
		
		do
		{
			$out=[];
			
			$c="ps ax | awk '{ print $1 }' | grep -e \"^{$pid[0]}$\"";
			
			exec($c,$out);
			
			if(empty($out)){				
				break;
			}
			
		}while(true);
		
		// 确保停止服务后swoole-task-pid文件被删除
		if(file_exists(SWOOLE_TASK_PID_PATH))
		{
			unlink(SWOOLE_TASK_PID_PATH);
		}
		
		if($isRestart)
		{
			$this->strcolor("  [ Success ] 执行命令 {$cmd} 成功，端口 {$this->opt->h}:{$this->opt->p} 进程结束",'cyan',null);
			
			$this->strcolor("重启SwooleServer服务",'green',null);
			
			return $this->serverstart();
		}
		else
		{
			$this->strcolor("  [ Success ] 执行命令 {$cmd} 成功，端口 {$this->opt->h}:{$this->opt->p} 进程结束",'green',null,1);
		}
	}
	
	/**
	 *  重启SwooleServer服务
	 */
	public function serverrestart()
	{
		$this->serverstop(true);
	}
	
	/**
	 *  列出swooleserver服务进程
	 */
	public function serverlist()
	{
		$this->strcolor("  [ Check ] 本机运行的{$this->opt->n}服务进程",'cyan',null);
	
		$cmd="ps aux|grep ".$this->opt->n."|grep -v grep|awk '{print $1, $2, $6, $8, $9, $11}'";
	
		exec($cmd,$out);
	
		if(empty($out))
		{
			$this->strcolor("  [ Error ] 没有发现正在运行的{$this->opt->n}服务进程",'red',null,1);
		}
	
		$this->strcolor("  [ List ] USER PID RSS(kb) STAT START COMMAND ",'green',null);
	
		foreach($out as $k=>$v)
		{
			$this->strcolor("  [ {$k} ] {$v} ",'green',null);
		}
	}
	
	/**
	 * 查看端口是否被占用
	 * @param int $port
	 */
	public function portBind($port)
	{
		$ret=[];
		
		$cmd="lsof -i :{$port}|awk '$1 != \"COMMAND\"  {print $1, $2, $9}'";
		
		exec($cmd,$out);
		
		if($out)
		{
			foreach($out as $v)
			{
				$a=explode(' ',$v);
				
				list ($ip,$p)=explode(':',$a[2]);
				
				$ret[$a[1]]=['cmd' => $a[0],'ip' => $ip,'port' => $p];
			}
		}
		
		return $ret;
	}
	
	/**
	 *  检查Client配置以及执行命令
	 */
	private function CheckClientCmd(){}
	
	/**
	 *  Client端操作说明
	 */
	public function clienthelp(){}
	
	
}
