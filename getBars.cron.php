<?php

// 添加每个用户的吧列表,为了能获取全,每次执行只获得一个人的列表

require_once 'cron.class.php';

require_once 'CURL.class.php';

require_once 'dataBase.class.php';

class GetBarsCron extends Cron{

	private $user = null;

	function __construct(){
		parent::__construct();
		
		$this->mysqli->table('user');
		$this->mysqli->fields('id,name,cookie');
		$this->mysqli->where('last < \'' . $this->toDay . '\'');
		$this->mysqli->limit(1);
		$this->user = $this->mysqli->select();
		$this->user = $this->user[0];
		if (!$this->user){die('no more new user');}
	}

	function updateDb($id,$last){
		$this->mysqli->clear();
		$this->mysqli->table('user');
		$this->mysqli->set('last = \'' . $last . '\'');
		$this->mysqli->where('id = ' . $id);
		if (!$this->mysqli->update()){
			die('mysqli error on update');
		}
	}

	function insertDb($data){
//		echo $data;
		if (preg_match_all('/href="\/f\?kw=([^"]+)/',$data,$what)){
			$bars = array();
			foreach ($what[1] as $key => $val){
				$bar['user'] = $this->user['id'];
				$bar['bar'] = $val;
				$bar['time'] = $this->toDay;
				$bars[] = $bar;
			}

			$this->mysqli->clear();
			$this->mysqli->table('bars');
			$this->mysqli->data($bars);
			$this->mysqli->insert();
			
			// 时间到就不再循环
			return $this->overTime() ? false : true ;
		} else {
			return false;
		}
	}

	function run(){
		if (!$this->user) {exit(0);}

		$page = 0;
		do {
			++$page;
			$this->curl->setUrl('http://tieba.baidu.com/f/like/mylike?pn=' . $page);
			$this->curl->setCookie($this->user['cookie']);
			$data = $this->curl->execute();
		} while ($this->insertDb($data));
		
		$this->updateDb($this->user['id'],$this->toDay);
		echo $this->user['name'] . '添加贴吧列表成功!';
	}

}

$cron = new GetBarsCron();
$cron->run();

?>
