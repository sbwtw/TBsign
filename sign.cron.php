<?php

// 签到
require_once 'cron.class.php';
require_once 'CURL.class.php';
require_once 'dataBase.class.php';

class SignCron extends Cron{

	private $barList = null;

	function __construct(){
		parent::__construct();

		$this->mysqli->table('user,bars');
		$this->mysqli->fields('bars.id as id, user.cookie as cookie, bars.bar as bar');
		$this->mysqli->where('user.id = bars.user and bars.exp in (0,-2) and bars.time = \'' . $this->toDay . '\'');
		$this->mysqli->order('bar');
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
			// 替换'&amp; => amp;'
			$addr = preg_replace('/&amp;/','&',$what[1]);

			$this->curl->setUrl('http://tieba.baidu.com' . $addr);
			$this->curl->setCookie($cookie);
			$this->curl->setUserAgent('Mozilla/5.0 (Linux; U; Android 2.3.4; zh-cn; W806 Build/GRJ22) AppleWebKit/530.17 (KHTML, like Gecko) FlyFlow/2.4 Version/4.0 Mobile Safari/530.17 baidubrowser/042_1.8.4.2_diordna_008_084/AIDIVN_01_4.3.2_608W/1000591a/9B673AC85965A58761CF435A48076629%7C880249110567268/1');
			$data = $this->curl->execute();

			if (preg_match('/"light">(\d+)<\/span/',$data,$what)){
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
