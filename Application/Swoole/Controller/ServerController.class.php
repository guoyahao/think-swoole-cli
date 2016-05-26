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

class ServerController extends IndexController
{
	/**
	 * SwooleServer 实例
	 * @var null|SwooleTaskServer
	 */
	private $SwooleServer = null;
	
	/**
	 * Swoole配置
	 * @var array
	 */
	private $SwooleSetting = [];
	
 	/**
 	 * 定义Swoole服务目录
 	 * @var sitring
 	 */
	static $SwoolePath = MODULE_PATH;
	
	/**
	 * 定义SWOOLE_TASK_PID_PATH 路径
	 * @var string
	 */
	static $SwooleTaskPidPath = MODULE_PATH.DIRECTORY_SEPARATOR.'Tmp'.DIRECTORY_SEPARATOR.'swoole-task.pid';
	
	/**
	 * 定义SWOOLE_TASK_NAME_PRE
	 * @var string
	 */
	static $SwooleTaskNamePre = 'SwooleServer';
 
	/**
	 * 定义目录分隔符
	 * @var /
	 */
	static $DS = DIRECTORY_SEPARATOR;
	
	public function _initialize($host='0.0.0.0',$port=9510,$daemon=0,$name='')
	{
		empty($_GET['host'])?$host = '0.0.0.0':$host = $_GET['host'];
		
		empty($_GET['port'])?$port = 9510:$port = $_GET['port'];
		
		empty($_GET['daemon'])?$daemon = 0:$daemon = $_GET['daemon'];
		
		empty($_GET['name'])?$name = self::$SwooleTaskNamePre :$daemon = self::$SwooleTaskNamePre.'-'.$_GET['daemon'];
		
		// swoole 配置文件目录路径
		
		$ConfigPath = self::$SwoolePath.'Conf';
		
		// swoole.ini 路径
		
		$ConfigFile = $ConfigPath.self::$DS.'swoole.ini';

		// 日志文件路径
		
		$TmpPath = self::$SwoolePath.'Tmp';

		// 首次启动初始化默认配置
		
		/*if(!file_exists($ConfigFile))
		{
			
		} */
		
		mkdir($ConfigPath);
			
		$setting=[
				// 监听ip
				'host' =>$host,
				// 监听端口
				'port' =>$port,
				// 环境 dev|test|prod
				'env' => 'dev',
				// swoole 进程名称
				'process_name' => self::$SwooleTaskNamePre,
				// 关闭Nagle算法,提高HTTP服务器响应速度
				'open_tcp_nodelay' => 1,
				// 是否守护进程 1=>守护进程| 0 => 非守护进程
				'daemonize' => $daemon,
				// worker进程 cpu 1-4倍
				'worker_num' => 16,
				// task进程
				'task_worker_num' => 10,
				// 当task进程处理请求超过此值则关闭task进程
				'task_max_request' => 10000,
				// task进程临时数据目录
				'tmp_dir' => $TmpPath,
					
				'log_dir' => $TmpPath.'/log',
					
				'task_tmpdir' => $TmpPath.'/task'
		];
			
		$iniSetting='[swoole]'.PHP_EOL;
			
		foreach($setting as $k=>$v)
		{
			$iniSetting.="{$k} = {$v}".PHP_EOL;
		}
			
		file_put_contents($ConfigFile,$iniSetting);
		
		// 首次启动创建临时目录
		
		if(!file_exists($TmpPath))
		{
			mkdir($TmpPath,0777);
			
			mkdir($TmpPath.self::$DS.'log',0777);
			
			mkdir($TmpPath.self::$DS.'task',0777);
		}
		
		// 加载配置文件内容
		
		if(!file_exists($ConfigFile))
		{
			throw new \ErrorException("swoole config file:{$ConfigFile} not found");
		}
		
		#TODO 是否需要检查配置文件内容合法性
		
		$ini = parse_ini_file($ConfigFile,true);
		
		$this->SwooleSetting = $ini['swoole'];
		
	}

	/**
	 *  入口文件
	 */
	public function index(){}
	
	public function _empty(){}
		
	/**
	 *  获取swoole配置
	 */
	public function getSetting()
	{
		return $this->SwooleSetting;
	}

	/**
	 * 设置swoole进程名称
	 * @param string $name swoole进程名称
	 */
	private function setProcessName($name)
	{
		if(function_exists('cli_set_process_title'))
		{
			cli_set_process_title($name);
		}
		else
		{
			if(function_exists('swoole_set_process_name'))
			{
				\swoole_set_process_name($name);
			}
			else
			{
				\trigger_error(__METHOD__." failed. require cli_set_process_title or swoole_set_process_name.");
			}
		}
	}
	
    
	/**
	 * 启动SwooleServer
	 * @param string $host IP
	 * @param string $port 端口 
 	 * @param int 	 $daemon 是否守护进程
	 * @param string $processName 进程名称
	 */
	public function ServRun()
	{		
		// 构建SwooleServer对象
		
		$this->SwooleServer = new \swoole_server($this->SwooleSetting['host'],$this->SwooleSetting['port']);

		// 获取运行时参数
		//$this->getSetting();	
		
		// 设置运行时参数
		
		$this->SwooleServer->set($this->SwooleSetting);
		
		// 注册事件回调函数
		
		$call=['start', 'workerStart', 'managerStart','Connect', 'Receive',  'task', 'finish', 'workerStop', 'shutdown','WorkerError'];
		
		// 事件回调函数绑定
		
		foreach($call as $v)
		{
			$m='on'.ucfirst($v);
			
			if(method_exists($this,$m))
			{
				$this->SwooleServer->on($v,[
						$this,$m
				]);
			}
		}
		
		// 启动服务器		
		$this->SwooleServer->start();
	}
	
	/**
	 * SwooleServer主进程启动 swoole-server master start
	 * @param $server
	 */
	public function onStart($server)
	{
		$this->writeLog('Date:' . date('Y-m-d H:i:s') . "\t SwooleServer master worker start\n", $this->SwooleSetting['log_dir'].'/swoole.log', $this->SwooleSetting['daemonize']);
		
		$this->setProcessName($server->setting['process_name'] . '-master');
		
		//记录进程id,脚本实现自动重启
		
		$pid = "{$this->SwooleServer->master_pid}\n{$this->SwooleServer->manager_pid}";
		
		$this->writeLog($pid, self::$SwooleTaskPidPath ,true);
	}
	
	/**
	 * manager worker start
	 * @param $server
	 */
	public function onManagerStart($server)
	{
		$this->writeLog('Date:' . date('Y-m-d H:i:s') . "\t SwooleServer manager worker start\n", $this->SwooleSetting['log_dir'].'/swoole.log', $this->SwooleSetting['daemonize']);
		
		$this->setProcessName($server->setting['process_name'] . '-manager');
	}
	
	/**
	 * 启动链接
	 * @param $server
	 */
	public function onConnect($server)
	{
		$this->writeLog('Date:' . date('Y-m-d H:i:s') . "\t SwooleServer master worker Connect\n", $this->SwooleSetting['log_dir'].'/swoole.log', $this->SwooleSetting['daemonize']);
	}
	
	/**
	 * 接收请求处理
	 * @param $server
	 * @param $fd
	 * @return mixed
	 */
	public function onReceive($server, $fd, $from_id, $data)
	{
		$Rdata = (array)json_decode($data);		
		
		//获取swoole服务的当前状态
		
	    if (isset($Rdata['cmd']) && $Rdata['cmd'] == 'status')
		{
			$server->send($fd,json_encode($this->SwooleServer->stats()),$from_id);
			
			$server->close();
			
			return true;
		}
		
		if(count($Rdata)>0)
		{
			foreach ($Rdata as $k=>$v)
			{
				$this->SwooleServer->task((array)$v);
			}
		}
		
		//TODO 非task请求处理
		//$this->SwooleServer->task($Rdata);
		
		$out = '[' . date('Y-m-d H:i:s') . '] ' . $data . PHP_EOL."\n";
				
		$server->send($fd,json_encode(array('Swoole Server Status:'=>$fd)),$from_id);
		
		//INFO 立即返回 非阻塞
		$server->close($fd,$out);
		
		return true;
	}
	
	/**
	 * 关闭SwooleServer主进程
	 * swoole-server master shutdown
	 */
	public function onShutdown()
	{
		unlink(self::$SwooleTaskPidPath);
		
		$this->writeLog('Date:' . date('Y-m-d H:i:s') . "\t SwooleServer shutdown\n", $this->SwooleSetting['log_dir'].'/swoole.log');
	}
	
	/**
	 * worker start 加载业务脚本常驻内存
	 * @param $server
	 * @param $workerId
	 */
	public function onWorkerStart($server, $workerId)
	{
		if ($workerId >= $this->setting['worker_num']) 
		{
			$this->setProcessName($server->setting['process_name'] . '-task');
		} 
		else 
		{
			$this->setProcessName($server->setting['process_name'] . '-event');
		}
	}
	
	/**
	 * worker 进程停止
	 * @param $server
	 * @param $workerId
	 */
	public function onWorkerStop($server, $workerId)
	{
		$this->writeLog('Date:' . date('Y-m-d H:i:s') . "\t SwooleServer [ {$server->setting['process_name']}]  worker:{$workerId} shutdown\n", $this->SwooleSetting['task_tmpdir'].'/task.log');
	}

	/**
	 * worker 进程错误
	 * @param $server
	 * @param $workerId
	 */
	public function onWorkerError($server, $worker_id , $worker_pid, $exit_code)
	{
		$this->writeLog('Date:' . date('Y-m-d H:i:s') . "\t \Task [worker_id:#{$worker_id}] failed, Error[$exit_code] \n", $this->SwooleSetting['task_tmpdir'].'/task_error.log', $this->SwooleSetting['daemonize']);
	}
	
	/**
	 * 任务处理
	 * @param $server
	 * @param $taskId
	 * @param $fromId
	 * @param $request
	 * @return mixed
	 */
	public function onTask($server, $taskid, $fromId, $data)
	{
		//任务执行 worker_pid实际上是就是处理任务进程的task进程id
		
		$class = new $data['class'];
		
		$class->$data['fun']($data['data']);
		
		$this->writeLog('Date:' . date('Y-m-d H:i:s') . "\t SwooleServer master worker on Task: worder_pid {$server->worker_pid} \n", $this->SwooleSetting['task_tmpdir'].'/task.log', $this->SwooleSetting['daemonize']);
		
		if (!isset($ret['workerPid'])) 
		{
			//处理此任务的task-worker-id			
			$ret['workerPid'] = $server->worker_pid;
		}
		
		$ret['taskid'] = $taskid;
		
		//INFO swoole-1.7.18之后return 就会自动调用finish
		return $ret; 
	}
	
	/**
	 * 任务结束回调函数
	 * @param $server
	 * @param $taskId
	 * @param $ret
	 */
	public function onFinish($server, $taskId, $ret)
	{
		$fromId = $server->worker_id;

		if (empty($ret['errno'])) 
		{
			//任务成功运行不再提示
			$this->writeLog('Date:' . date('Y-m-d H:i:s') . "\t Task [taskId:{$taskId}] 执行成功  \n", $this->SwooleSetting['task_tmpdir'].'/task.log', $this->SwooleSetting['daemonize']);			
		} 
		else 
		{
			$error = PHP_EOL . var_export($ret, true);
			
			$this->writeLog('Date:' . date('Y-m-d H:i:s') . "\t Task [taskId:$fromId#{$taskId}] failed, Error[$error]\n", $this->SwooleSetting['task_tmpdir'].'/task_error.log', $this->SwooleSetting['daemonize']);			
		}
	}
}


