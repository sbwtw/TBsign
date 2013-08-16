<?php

require_once 'CURL.class.php';
require_once 'dataBase.class.php';

abstract class Cron{
	
	private $startTime = null;
	private $maxRunTime = null;

	public $toDay = null;
	public $curl = null;
	public $mysqli = null;

	function __construct(){
		$this->startTime = date('U');
		$this->toDay = date('Y-m-d');
		$this->maxRunTime = 25;

		$this->curl = new CURL(5000);
		$this->mysqli = new DataBase();
	}

	function overTime(){
		if (date('U') - $this->startTime > $this->maxRunTime){
			return true;
		} else {
			return false;
		}
	}
}

?>
