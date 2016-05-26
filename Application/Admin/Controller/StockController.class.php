<?php
// +----------------------------------------------------------------------
// | 股票管理控制器
// +----------------------------------------------------------------------

namespace Admin\Controller;

class StockController extends AdminController {

    /**
     * 股票列表
     */
	public function stockList(){
		$map = array();
		
		//搜索条件
		$s_type = (int)I('get.s_type', 0);
		$keywords = trim(I('get.keywords', ''));
		if (!empty($keywords)) {
			switch ($s_type){
				case 1:
					$map['stock_code'] = $keywords;
					break;
				case 2:
					$map['stock_name'] = array('like', '%'.$keywords.'%');
					break;
				case 3:
					$map['sina_industry'] = array('like', '%'.$keywords.'%');
					break;
			}
		}
		
		//C('LIST_ROWS', 20);
		$list = $this->lists('associationtable', $map, 'stock_code ASC');
		$this->assign('_list', $list);
		
		$this->meta_title = '股票列表';
		$this->display();
	}
	
	/**
	 * 个股详情
	 */
	public function singlestockList(){
		$stockcode = I('get.stockcode', '');
		$f = I('get.f', '');
		$map = array();
		
		if(empty($stockcode)){
			$this->error('股票不存在', U('Stock/stockList'));
		}
		
		$table = M('associationtable')->where(array('stock_code'=>$stockcode))->find();
		$tablename = $table['table_name'];
		if (empty($tablename)) {
			$this->error('股票不存在', U('Stock/stockList'));
		}
		
		//搜索条件
		$time_s = I('get.time_s', '');
		$time_e = I('get.time_e', '');
		if (!empty($time_s) && !empty($time_e)) {
			$map['_string'] = 'trade_date>="'.$time_s.'" and trade_date<="'.$time_e.'"';
		}elseif (!empty($time_s) && empty($time_e)) {
			$map['trade_date'] = array('egt', $time_s);
		}elseif (empty($time_s) && !empty($time_e)) {
			$map['trade_date'] = array('elt', time_e);
		}
		
		//字段信息
		$fieldData = $this->_getFields();
		
		$mod = M($tablename);//创建模型对象
		
		//处理显示信息
		if (!empty($f)) {
			$f = rtrim($f, '|');
			$f = explode('|', $f);
		}else{
			$f = array(0,1,2,3,6,7,8,9);//默认显示
		}
		$this->assign('f', $f);
		
		$field = '';
		$titleArr = array();//显示的字段标题
		foreach ($f as $v){
			$field .= $fieldData[$v]['key'].',';
			$titleArr[] = $fieldData[$v]['val'];
		}
		$field = rtrim($field, ',');

		$list = $this->lists($mod, $map, 'trade_date DESC', $field);
		//var_dump($mod->getLastSql());
		foreach ($list as $k=>$v){
			$list[$k] = array_values($v);
		}
		//var_dump($list);
		$this->assign('_list', $list);
		
		$this->assign('fielddata', $fieldData);//所有字段
		$this->assign('titledata', $titleArr);
		
		$this->assign('field_url', U('singlestockList',array('stockcode'=>$stockcode,'p'=>$_GET['p'])));
		$this->meta_title = $table['stock_name'];
		$this->display();
	}
	
	/**
	 * 字段信息
	 */
	private function _getFields(){
		$fieldData = array(
				array(
						'key' => 'stock_code',
						'val' => '股票代码'
				),
				array(
						'key' => 'stock_name',
						'val' => '股票名称'
				),
				array(
						'key' => 'trade_date',
						'val' => '交易日期'
				),
				array(
						'key' => 'sina_industry',
						'val' => '新浪行业'
				),
				array(
						'key' => 'sina_concept',
						'val' => '新浪概念'
				),
				array(
						'key' => 'sina_area',
						'val' => '新浪地域'
				),
				array(
						'key' => 'opening_price',
						'val' => '开盘价'
				),
				array(
						'key' => 'highest_price',
						'val' => '最高价'
				),
				array(
						'key' => 'lowest_price',
						'val' => '最低价'
				),
				array(
						'key' => 'closing_price',
						'val' => '收盘价'
				),
				array(
						'key' => 'houfuquan_price',
						'val' => '后复权价'
				),
				array(
						'key' => 'qianfuquan_price',
						'val' => '前复权价'
				),
				array(
						'key' => 'change_rate',
						'val' => '涨跌幅'
				),
				array(
						'key' => 'volume',
						'val' => '成交量'
				),
				array(
						'key' => 'turnover',
						'val' => '成交额'
				),
				array(
						'key' => 'turnover_rate',
						'val' => '换手率'
				),
				array(
						'key' => 'circulated_market',
						'val' => '流通市值'
				),
				array(
						'key' => 'total_market',
						'val' => '总市值'
				),
				array(
						'key' => 'is_limitup',
						'val' => '是否涨停'
				),
				array(
						'key' => 'is_limitdown',
						'val' => '是否跌停'
				),
				array(
						'key' => 'pe_ttm',
						'val' => '市盈率TTM'
				),
				array(
						'key' => 'ps_ttm',
						'val' => '市销率TTM'
				),
				array(
						'key' => 'pf_ttm',
						'val' => '市现率TTM'
				),
				array(
						'key' => 'pb_ratio',
						'val' => '市净率'
				),
				array(
						'key' => 'ma_5',
						'val' => 'MA_5'
				),
				array(
						'key' => 'ma_10',
						'val' => 'MA_10'
				),
				array(
						'key' => 'ma_20',
						'val' => 'MA_20'
				),
				array(
						'key' => 'ma_30',
						'val' => 'MA_30'
				),
				array(
						'key' => 'ma_60',
						'val' => 'MA_60'
				),
				array(
						'key' => 'ma_str',
						'val' => 'MA金叉死叉'
				),
				array(
						'key' => 'macd_dif',
						'val' => 'MACD_DIF'
				),
				array(
						'key' => 'macd_dea',
						'val' => 'MACD_DEA'
				),
				array(
						'key' => 'macd_macd',
						'val' => 'MACD_MACD'
				),
				array(
						'key' => 'macd_str',
						'val' => 'MACD_金叉死叉'
				),
				array(
						'key' => 'kdj_k',
						'val' => 'KDJ_K'
				),
				array(
						'key' => 'kdj_d',
						'val' => 'KDJ_D'
				),
				array(
						'key' => 'kdj_j',
						'val' => 'KDJ_J'
				),
				array(
						'key' => 'kdj_str',
						'val' => 'KDJ_金叉死叉'
				),
				array(
						'key' => 'boll_center',
						'val' => '布林线中轨'
				),
				array(
						'key' => 'boll_up',
						'val' => '布林线上轨'
				),
				array(
						'key' => 'boll_down',
						'val' => '布林线下轨'
				),
				array(
						'key' => 'psy',
						'val' => 'psy'
				),
				array(
						'key' => 'psyma',
						'val' => 'psyma'
				),
				array(
						'key' => 'rsi1',
						'val' => 'rsi1'
				),
				array(
						'key' => 'rsi2',
						'val' => 'rsi2'
				),
				array(
						'key' => 'rsi3',
						'val' => 'rsi3'
				)
		);
		return $fieldData;
	}

}