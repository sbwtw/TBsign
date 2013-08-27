<!DOCTYPE html>
<html lang="zh-CN">
<head>
	<title>登陆</title>
	<meta charset='utf-8'>
</head>
<body>
	<div>
		<div><span>百度帐号:</span><input id='username' type='text'/></div>
		<div><span>帐号密码:</span><input id='password' type='password'/></div>
		<div id='verify' style='display:none;'><span>验证码:</span><input type='text' id='verifycode'/><img id='verifyImg'/></div>
		<div><input id='submit' type='button' value='登陆' onclick='submit();'/></div>
	</div>
	<hr>
	<a href='/manage.php' title='帐号管理' target='_blank'>查看签到帐号日志</a>
  <div>
  	<br>说明：
  	<ul>
  		<li>账号、密码填写百度账号密码，服务器以md5盐化加密保存密码。</li>
  		<li>账号日志页面登陆也是用这个账号密码。</li>
  		<li>本系统开源，代码地址：<a href='https://github.com/sbwtw/TBsign' target='_blank' title='github'>百度贴吧签到系统</a></li>
  		<?php //<!--请保留链接--> ?>
  		<li>作者博客：<a href='http://blog.sbw.so' title='石博文博客' target='_blank'>石博文博客</a></li>
  	</ul>
  </div>
</body>

<script language='javascript'>
	var step = 1;
	var token="";
	var verifyCode="";
	var verifyAddress="";
	var cookie="";

	function $(str){
		return document.getElementById(str);
	}

	function XHRCallBack(responseText){
		$('submit').style.display='';

		var res = eval('(' + responseText + ')');


		// 需要验证码
		if (res.token){
			step = 2;
			token=res.token;
			verifyAddress=res.verifyAddress;
			cookie=res.cookie;
			
			$('verify').style.display='';
			$('verifyImg').src='https://passport.baidu.com/cgi-bin/genimage?' + res.verifyAddress;
		} else {
			// 成功得到cookie
//			document.write('你的cookie:<br/><hr/><textarea style="width:100%;">' + res.cookie + "</textarea>");
			if (res.result == 1 && res.info == 'ok'){
				alert('登陆成功!\n' + '管理密码: 为百度登陆密码');
//				document.write('你的管理密码: <input type="text" value=' + res.password + '>');
			} else {
				alert('登陆失败! \n原因:' + res.info + '\n错误码:' + res.result);
			}
		}
	}

	function submit(){

		$('submit').style.display='none';
		
		var postStr='userName=' + $('username').value;
		postStr+='&password=' + $('password').value;
		postStr+='&verifyCode=' + $('verifycode').value;
		postStr+='&step=' + step;
		postStr+='&cookie=' + cookie;
		postStr+='&token=' + token;
		postStr+='&verifyAddress=' + verifyAddress;
	
		XHR=new XMLHttpRequest();
		XHR.open('POST','/getCookie.action.php',true);
		XHR.setRequestHeader("Content-type","application/x-www-form-urlencoded");
		XHR.send(postStr);
		XHR.onreadystatechange=function(){
			if (XHR.readyState==4 && XHR.status==200){
				XHRCallBack(XHR.responseText);
			}
		}
	}
</script>
</html>
