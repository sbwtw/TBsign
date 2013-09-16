<?php

class DataBase {
	
	private $mysqli = null;
	private $fields = '*';
	private $table = null;
	private $where = null;
	private $group = null;
	private $order = null;
	private $limit = null;
	private $data = null;
	private $set = null;

	function __construct(){
		// config
		$config = require 'dataBaseConfig.include.php';
		
		$this->mysqli = new mysqli($config['host'],$config['user'],$config['password'],$config['dbName'],$config['port']);

		if ($this->mysqli->connect_error){
			die('{"info":"mysqli 连接失败","result":"-1"}');
		} else {
			$this->mysqli->set_charset('utf8');
		}
	}

	function __destruct(){
		if ($this->mysqli){
			$this->mysqli->close();
		}
	}

	private function execute($sql){
//		echo $sql . '<br>';
		$res = $this->mysqli->query($sql);

		if ($res === true){
			return true;
		} else if ($res){
			$data = array();
			while ($row = $res->fetch_assoc()){
				$data[] = $row;
			}

			$res->free();
			return $data;
		} else {
			return false;
		}
	}

	public function escape(&$str){
		$str = $this->mysqli->real_escape_string($str);
		return $str;
	}

	function clear(){
		$this->fields = '*';
		$this->table = null;
		$this->where = null;
		$this->group = null;
		$this->order = null;
		$this->limit = null;
		$this->data = null;
		$this->set = null;
	}

	function fields($str){
		$this->fields = $str;
	}

	function table($str){
		$this->table = $str;
	}

	function where($str){
		$this->where = $str;
	}

	function group($str){
		$this->group = $str;
	}

	function order($field,$direction = 'asc'){
		$this->order['field'] = $field;
		$this->order['direction'] = $direction;
	}

	function limit($rows,$offset = 0){
		$this->limit['offset'] = $offset;
		$this->limit['rows'] = $rows;
	}

	function set($str){
		$this->set = $str;
	}

	// 数据类型: array(array('key' => 'val','key2' => 'val2'),array('key' => 'val','key2' => 'val2'),...);
	function data($unionMapData){
		$fields = array();
		if (!isset($unionMapData[0])){return;}

		foreach ($unionMapData[0] as $key => $val){
			$fields[] = $this->escape($key);
		}

		$items = array();
		foreach ($unionMapData as $key){
			$item = array();
			foreach ($fields as $k){
				$item[] = '\'' . $this->escape($key[$k]) . '\'';
			}
			$items[] = '(' . join(',',$item) . ')';
		}

		$this->data['fields'] = '(' . join(',',$fields) . ')';
		$this->data['items'] = join(',',$items);
	}

	function select(){
		$sql = 'select ' . $this->fields . ' from ' . $this->table;
		if ($this->where){
			$sql .= ' where ' . $this->where;
		}
		if ($this->group){
			$sql .= ' group by ' . $this->group;
		}
		if ($this->order){
			$sql .= ' order by ' . $this->order['field'] . ' ' . $this->order['direction'];
		}
		if ($this->limit){
			$sql .= ' limit ' . $this->limit['offset'] . ',' . $this->limit['rows'];
		}
		return $this->execute($sql);
	}

	function insert(){
		$sql = 'insert into ' . $this->table . ' ' . $this->data['fields'] . ' values ' . $this->data['items'];
		
		return $this->execute($sql);
	}

	function update(){
		$sql = 'update ' . $this->table . ' set ' . $this->set . ' where ' . $this->where;
		return $this->execute($sql);
	}

	function delete(){
		$sql = 'delete from ' . $this->table . ' where ' . $this->where;
		return $this->execute($sql);
	}

}

?>
