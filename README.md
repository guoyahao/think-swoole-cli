# think-swoole-cli  swoole-server-task

框架：thinkphp 

入口文件：swoole.php

主要处理模块：Application\Swoole

tcp服务：php Swoole 扩展

使用：

进入当前文件：

cd /think-swoole-cli

php swoole.php cmd -t server -e help

检查Server配置：

  [ Setting ] 指定此参数 [ -e ] 命令为: help
  
执行Server命令：help

  命令:
  
       [ -t | -e | -d | -h | -p | -n ]		
       
  参数:		
  
      -t 选择参数值：server/client
			
      -e 可执行命令
      
                   启动-e start 启动SwooleServer服务	
	
                   关闭-e stop  停止SwooleServer服务 [ 必须指定端口 ]	
	
                   重启-e restart 重启SwooleServer服务			
	
                   获取-e list  列出SwooleServer服务进程
	
      -d 指定此参数,以守护进程模式运行  (1:守护 0:不守护 )例如: php swoole.php cmd -t server -e start -d 1 默认: 1 
		
      -h 指定监听ip,例如 php swoole.php cmd -t server -e start -h 127.0.0.1 默认：0.0.0.0
		
      -p 指定监听端口port， 例如 php swoole.php cmd -t server -e start -h 127.0.0.1 -p 9510
		
      -n 指定服务进程名称，例如 php swoole.php cmd -t server -e start -n test, 则进程名称为SwooleServer-test	

php swoole.php cmd -t server -e start

