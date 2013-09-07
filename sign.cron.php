<?php

// 签到
require_once 'cron.class.php';
require_once 'CURL.class.php';
require_once 'dataBase.class.php';

class SignCron extends Cron{

	private $barList = null;

	function __construct(){
		parent::__construct();

		// 设定任务个数和当前任务编号
		$threadCount = preg_match('/\d+/',$_GET['threadCount'],$what) ? $what[0] : 1;
		$threadNum = preg_match('/\d+/',$_GET['threadNum'],$what) ? $what[0] : 0;

		$this->mysqli->table('user,bars');
		$this->mysqli->fields('bars.id as id, user.cookie as cookie, bars.bar as bar');
		$this->mysqli->where('user.id = bars.user and bars.id % \'' . $threadCount . '\' = \'' . $threadNum . '\' and bars.exp in (0,-2) and bars.time = \'' . $this->toDay . '\'');
		$this->mysqli->order('rand');
		$this->mysqli->limit(30);
		$this->barList = $this->mysqli->select();
		if (!$this->barList){die('no more bar need sign');}
	}

	function updateDb($id,$exp){
		$this->mysqli->clear();
		$this->mysqli->table('bars');
		$this->mysqli->set('exp = ' . ($exp > 0 ? $exp : ' exp + ' . $exp));
		$this->mysqli->where('id = ' . $id);
		$this->mysqli->update();
	}

	function sign($id,$bar,$cookie){
		$this->curl->setUrl('http://tieba.baidu.com/mo/m?kw=' . $bar);
		$this->curl->setCookie($cookie);
		$this->curl->setUserAgent('Mozilla/5.0 (Linux; x86_64;) firefox 26.0 Gecko');
		$data = $this->curl->execute();

		if (preg_match('/<a\shref="([^"]+)">签到/',$data,$what)){
			
			// BDUSS
			preg_match('/BDUSS=([^;]+)/',$cookie,$what);
			$postData['BDUSS'] = $what[1];
			$postData['_client_id'] = '04-00-DA-69-15-00-73-97-08-00-02-00-06-00-3C-43-01-00-34-F4-22-00-BC-35-19-01-5E-46';
			$postData['_client_type'] = '4';
			$postData['_client_version'] = '1.2.1.17';
			$postData['_phone_imei'] = '641b43b58d21b7a5814e1fd41b08e2a5';

			// fid
			preg_match('/"fid" value="(\w+)/',$data,$what);
			$postData['fid'] = $what[1];
			$postData['kw'] = iconv('gbk','utf-8',urldecode($bar));
			$postData['net_type'] = '3';
			
			// 获取tbs
			preg_match('/"tbs" value="(\w+)/',$data,$what);
			$postData['tbs'] = $what[1];

			$sign = '';
			foreach ($postData as $key => $val){
				$sign .= $key . '=' . $val;
			}

			// 加密
			$postData['sign'] = md5($sign . 'tiebaclient!!!');

			$this->curl->setUrl('http://c.tieba.baidu.com/c/c/forum/sign');
			$this->curl->setCookie($cookie);
			$this->curl->setPost($postData);
			$data = $this->curl->execute();

			if (preg_match('/sign_bonus_point":(\d+)/',$data,$what)){
				$this->updateDb($id,$what[1]);
			} else {
				// 签到执行但未返回经验
				$this->updateDb($id,-2);
			}
		} else {
			$this->updateDb($id,-1);
		}
	}

	function run(){
		foreach ($this->barList as $val){
			$this->sign($val['id'],$val['bar'],$val['cookie']);
			if ($this->overTime()) {exit(0);}
		}
	}

}

$sign = new SignCron();
$sign->run();


?>
