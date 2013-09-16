<?php

require_once 'CURL.class.php';

class Cookie{

	private $cookie = null;

	function setCookie($str){
		if (preg_match_all('/Set-Cookie: ([^=]+)=([^;]+)/',$str,$what)){
			foreach ($what[1] as $key => $val){
				$this->cookie[$what[1][$key]] = $what[2][$key];
			}
		}
	}

	// 专用于第二步
	function setCookie2($str){
		if (preg_match_all('/(\w+?)=([^;]+)/',$str,$what)){
			foreach ($what[1] as $key => $val){
				$this->cookie[$what[1][$key]] = $what[2][$key];
			}
		}
	}

	function getAll(){
		$res = '';
		foreach ($this->cookie as $key => $val){
			$res .= $key . '=' . $val . '; ';
		}

		return $res;
	}

	function getCookie($list){
		$res = '';
		foreach ($list as $key){
			$res .= $key . '=' . $this->cookie[$key] . '; ';
		}

		return $res;
	}

}

class Work{
	private $config;
	private $args;
	private $cookie;
	private $curl;
	private $returns;

	function __construct(){
		// 加载配置文件
		$this->config = require 'getCookieConfig.include.php';

		// 加载参数
		foreach ($this->config['args'] as $arg){
			if (isset($_POST[$arg])){
				$this->args[$arg] = $_POST[$arg];
			}
		}

		// 验证参数
		if (!isset($this->args['userName']) || !isset($this->args['password'])){
			$this->error('参数不足!');
		}

		// 不在第二步时将验证信息清空
		if (!isset($this->args['step']) || $this->args['step'] != 2){
			$this->args['verifyAddress'] = '';
			$this->args['verifyCode'] = '';
		}
	
		// 实例化类
		$this->cookie = new Cookie();
		$this->curl = new CURL();
	}

//	// 2013-09-17 百度修改登录机制
//	// 检查是否需要验证码
//	function needVerify($token){
//
//		$this->curl->setUrl(sprintf($this->config['verifyUrl'],$token,$this->args['userName']));
//		$this->curl->setCookie($this->cookie->getAll());
//		$data = $this->curl->execute();
//		$this->cookie->setCookie($data);
//
//		if (preg_match('/(captchaservice\w{200,})/',$data,$what)){
//			return $what[1];
//		} else {
//			return false;
//		}
//	}

	// 保存到数据库
	function save(){
		require_once 'dataBase.class.php';

		$db = new DataBase();
		$db->table('user');
		$db->data(array(array('password' => md5($this->args['userName'] . md5($this->returns['password'])),'name' => $this->args['userName'],'cookie' => $this->returns['cookie'])));
		return $db->insert();
	}

	// 登陆函数
	function login(){
		$data['apiver']='v3';
		$data['callback']='parent.bd__pcbs__sbw';
		$data['charset']='UTF-8';
		$data['codestring']=$this->args['verifyAddress'];
		$data['isPhone']='false';
		$data['logintype'] = 'bascilogin';
		$data['mem_pass']='on';
		$data['password']=$this->args['password'];
		$data['ppui_logintime']='8888';
		$data['quick_user'] = '0';
		$data['safeflg']='0';
		$data['splogin'] = 'rate';
		$data['staticpage']='http://tieba.baidu.com/tb/static-common/html/pass/v3Jump.html';
		$data['token']=$this->args['token'];
		$data['tpl']='tb';
		$data['tt'] = time() . '520';
		$data['u']='http://tieba.baidu.com/';
//		$data['usernamelogin']='1';
		$data['username']=$this->args['userName'];
		$data['verifycode']=$this->args['verifyCode'];

		$this->curl->setUrl($this->config['loginUrl']);
		$this->curl->setPost($data);
		$this->curl->setCookie($this->cookie->getAll());

		$res = $this->curl->execute();

		if (preg_match('/err_no=(\d+)/',$res,$what)){
			// 2013-09-17 百度更新验证码机制,验证码判断放在此处
			if ($what[1]){
				if (preg_match('/(captchaservice\w{200,})/',$res,$what)){
					// 需要验证码
					$this->returns['cookie'] = $this->cookie->getAll();
					$this->returns['verifyAddress'] = $what[1];
					$this->returns['token'] = $this->args['token'];
				}
				
				$this->error('验证错误',$what[1]);
			} else {
				// 登陆成功,获得最终cookie
				$this->cookie->setCookie($res);
				$this->curl->setUrl($this->config['finalUrl']);
				$this->curl->setCookie($this->cookie->getAll());

				$res = $this->curl->execute();

				// 成功
				$this->cookie->setCookie($res);
				$this->returns['cookie'] = $this->cookie->getCookie(array('BAIDUID','BDUSS','TIEBAUID'));
				$this->returns['password'] = $this->args['password'];

				if ($this->save()){
					$this->success('ok');
				} else {
					$this->error('添加帐号失败',-2);
				}
			}
		} else {
			$this->error('未知错误',-1);
		}
	}

	// 启动函数
	function init(){
		if (!isset($this->args['step']) || $this->args['step'] == 1){
			// 第一步
			// 第一次,获得BAIDUID
			$this->curl->setUrl(sprintf($this->config['tokenUrl'], time() . '520'));
			$data = $this->curl->execute();
			$this->cookie->setCookie($data);
			// 第二次,获得token
			$this->curl->setUrl(sprintf($this->config['tokenUrl'], time() . '520'));
			$this->curl->setCookie($this->cookie->getAll());
			$data = $this->curl->execute();
			$this->cookie->setCookie($data);
			if (preg_match('/"token" : "(\w+)"/',$data,$what)){
				$token = $what[1];
			} else {
				$this->error('get token error');
			}

			// 2013-09-17 应对百度修改登录机制,这里不再检查验证码,直接登录
//			if (preg_match('/(captchaservice\w{200,})/',$data,$what)){
//				// 需要验证码
//				$this->returns['cookie'] = $this->cookie->getAll();
//				$this->returns['verifyAddress'] = $what[1];
//				$this->returns['token'] = $token;
//
//				$this->success('next');
//			} else {
				// 直接登陆,要传递一个token
				$this->args['token'] = $token;
				$this->login();
//			}
		} else {
			// 第二步,直接登陆
			$this->cookie->setCookie2($_POST['cookie']);
			$this->login();
		}
	}

	// 成功
	function success($str=''){
		$this->returns['result'] = 1;
		$this->returns['info'] = $str;
		echo json_encode($this->returns);
		exit(0);
	}

	// 出错
	function error($str = '',$code = 0){
		$this->returns['result'] = $code;
		$this->returns['info'] = $str;
		die(json_encode($this->returns));
	}
}


$work = new Work();
$work->init();


?>
