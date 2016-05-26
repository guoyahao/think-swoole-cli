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

class ClientController extends Controller
{
	public function _empty()
	{
		parent::_empty();
	}
		
	/**
	  *  测试方法
	  */
	public function index()
	{
		$SendData = array(
						'class'=>'Swoole\Controller\TestController', // task 任务类
						'fun'=>'writecvs', // task 任务类-具体处理的方法
						'data'=>'1123131313' // c参数
				);
		
		// 先来1000个试试
 		for ($i=0;$i<=999;$i++)
		{
			$this->task(json_encode($SendData));
		}
	}
	
	
	public function task($data)
	{
		$client = new \swoole_client(SWOOLE_SOCK_TCP,SWOOLE_SOCK_ASYNC); // 异步非阻塞
		
		$client->on("connect",function ($cli) use ($data)
		{
			echo "SwooleClinet connected\n";
			
			$cli->send($data);
			
		});
		
		$client->on("receive",function ($cli,$data)
		{
 			if(empty($data))
			{
				$cli->close();
				
				echo "SwooleClinet closed\n";
			}
			else
			{
				echo "received: \n";				
			} 
		});
		
		$client->on("error",function ($cli)
		{
			exit("error\n");
		});
		
		$client->on("close",function ($cli,$data)
		{
			echo "connection is closed : {$data} \n";
		});
		
		$client->connect('0.0.0.0',9510,0.5);
	}
	
	
	
}

?>