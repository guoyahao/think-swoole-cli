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
 * SwooleServer服务公共函数类
 */
class IndexController extends Controller
{
	/**
	 * 命令行参数
	 * @var Object
	 */
	var $opt;
	
	/**
	 *  入口判断
	 */
	public function _initialize()
	{
		// 判断是否是命令行模式
		if(!IS_CLI) die('禁止访问！');
		
		$this->opt = (object)$_GET;
		
		if(empty($this->opt->h)){
			$this->opt->h = '0.0.0.0';
		}
		
		if(empty($this->opt->p)){
			$this->opt->p = 9510;
		}
		
		if(empty($this->opt->d)){
			$this->opt->d = 1;
		}
		
		if(empty($this->opt->n)){
			$this->opt->n = 'SwooleServer';
		}
	}
	
	/**
	 * 记录日志或者打印日志
	 * @param string $log
	 * @param string $file_path
	 * @param string $write
	 */
	public function writeLog($log='',$file_path='',$write=false)
	{
		if(!$write){
			echo $log;
		}else{
			file_put_contents($file_path, $log,FILE_APPEND);
		}
	}

	/**
	 * 提示信息颜色
	 * @param string $str
	 * @param string $fgcolor
	 * @param string $bgcolor
	 * @return string
	 */
	public function strcolor($str,$fgcolor="white",$bgcolor=null,$exit=0)
	{
		static $fgcolors = array('black' => '0;30',
				'dark gray' => '1;30',
				'blue' => '0;34',
				'light blue' => '1;34',
				'green' => '0;32',
				'light green' => '1;32',
				'cyan' => '0;36',
				'light cyan' => '1;36',
				'red' => '0;31',
				'light red' => '1;31',
				'purple' => '0;35',
				'light purple' => '1;35',
				'brown' => '0;33',
				'yellow' => '1;33',
				'light gray' => '0;37',
				'white' => '1;37');
		static $bgcolors = array(
				'black' => '40',
				'red' => '41',
				'green' => '42',
				'yellow' => '43',
				'blue' => '44',
				'magenta' => '45',
				'cyan' => '46',
				'light gray' => '47',);
		$out="";
		if (!isset($fgcolors[$fgcolor]))
			$fgcolor='white';
			if (!isset($bgcolors[$bgcolor]))
				$bgcolor=null;
				if ($fgcolor)
					$out .= "\033[{$fgcolors[$fgcolor]}m";
					if ($bgcolor)
						$out .= "\033[{$bgcolors[$bgcolor]}m";
						$out .= $str . "\033[0m";
						echo $out."\n"; usleep(100000);
						if($exit) die;
	}
	
	
}