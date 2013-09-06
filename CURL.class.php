<?php

class CURL {

	private $curl = null;
	private $url = null;
	private $cookie = null;
	private $timeOut = 5000;

	function __construct($timeOut = 5000){
		$this->curl = curl_init();
		$this->timeOut = $timeOut;
	}

	function __destruct(){
		curl_close($this->curl);
	}

	function clear(){
		$this->url = null;
		$this->cookie = null;
		curl_close($this->curl);
		$this->curl = curl_init();
	}

	function setUrl($url){
		$this->url = $url;
		return $this;
	}

	function setHeader($header){
		curl_setopt($this->curl,CURLOPT_HTTPHEADER,$header);
	}

	function setCookie($cookie){
		$this->cookie = $cookie;
		return $this;
	}
	
	function setUserAgent($ua){
		curl_setopt($this->curl,CURLOPT_USERAGENT,$ua);
	}

	function setPost($mapData){
		curl_setopt($this->curl,CURLOPT_POST,true);
		curl_setopt($this->curl,CURLOPT_POSTFIELDS,$mapData);
	}

	function execute(){
		if (!$this->url) return 'url is null';
		curl_setopt($this->curl,CURLOPT_URL,$this->url);
		curl_setopt($this->curl,CURLOPT_HEADER,true);
		curl_setopt($this->curl,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($this->curl,CURLOPT_REFERER,'http://tieba.baidu.com/#');
		curl_setopt($this->curl,CURLOPT_FOLLOWLOCATION,false);
		curl_setopt($this->curl,CURLOPT_TIMEOUT_MS,$this->timeOut);
		$this->cookie && curl_setopt($this->curl,CURLOPT_COOKIE,$this->cookie);
		
		$data = curl_exec($this->curl);
		$this->clear();

		if (curl_errno($this->curl)){
//			print curl_error($this->curl);
			die('{"info":"curl 超时","result":"-1"}');
			return false;
		} else {
			return $data;
		}
	}
}

?>
