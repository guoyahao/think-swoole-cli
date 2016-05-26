<?php
// +----------------------------------------------------------------------
// | 导入CSV文件
// +----------------------------------------------------------------------
// | Copyright (c) 2013 http://www.swoole.com All rights reserved.
// +----------------------------------------------------------------------
// | Author: 郭亚浩 <515449117@qq.com> <http://www.guoyahao.com>
// +----------------------------------------------------------------------

namespace Swoole\Controller;

use Think\Controller;
/**
 入口控制器
 */
class TestController extends Controller
{
	/**
	 * 导入csv文件
	 * @param string $filePath
	 * @return boolean
	 */
	public function writecvs()
	{
		file_put_contents(APP_DATA_PATH."swoole-test/".time().".log",'writecvs is ok');
	}
}
