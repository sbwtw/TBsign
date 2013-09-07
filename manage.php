<?php
$logList = null;

if (isset($_GET['userName']) && isset($_GET['password'])){

	require_once 'dataBase.class.php';

	// 时间
	$time = preg_match('/\d{4}-\d{2}-\d{2}/',$_GET['time'],$what) ? $what[0] : date('Y-m-d');

	$mysqli = new DataBase();
	$mysqli->table('user,bars');
	$mysqli->fields('bars.bar as bar, bars.exp as exp');
	$mysqli->where('user.id = bars.user and bars.time = \'' . $time . '\' and user.name = \'' . $mysqli->escape($_GET['userName']) . '\' and password = \'' . md5($_GET['userName'] . md5($_GET['password'])) . '\'');
	$logList = $mysqli->select();

	// 区分 null 和 false
	$logList || $logList = false;
}

?>

<!DOCTYPE html>
<html lang='zh-CN'>
<head>
	<title>节操查看</title>
	<meta charset='utf-8'>
</head>
<body>
<?php if (isset($logList)){
	// 登陆失败
	$logList || die('登录失败 或 还没有拉取吧列表'); 
?>
	<table style="text-align:center;margin:0 auto;">
	<tr><th>吧名</th><th>节操</th></tr>
	<?php foreach ($logList as $i){
		echo '<tr><td><a target="_blank" href="http://tieba.baidu.com/f?kw=' . $i['bar'] . '">' . iconv('gbk','utf-8',urldecode($i['bar'])) . '吧</a></td><td>' . $i['exp'] . '</td></tr>';
	} ?>
	</table>
	<div>注:<br>节操0表示还没有执行签到<br>节操为-1表示已经签过或不能签到<br>节操为-2表示签到失败<br>节操为-4表示失败后补签失败</div>
	<hr>
	<div>提示：可ctrl+D书签本页面每日查看签到日志<br>url加&amp;time=xxxx-xx-xx可查看指定日期日志。</div>
	<form style="display:none;" id='deleteAccount' action='/deleteAccount.action.php' method='post' taegt='_self'>
		<div><span>帐号:</span><input name='userName' type='text'></div>
		<div><span>密码:</span><input name='password' type='password'></div>
		<div><button type='submit'>确定删除帐户</button></div>
	</form>
	<div><a href='#' title='删除帐号' onclick='document.getElementById("deleteAccount").style.display="";'>删除帐号</a></div>
<?php } else { ?>
	<form action='#' method='get' target='_self'>
		<div><span>百度帐号:</span><input name='userName' type='text'></div>
		<div><span>管理密码:</span><input name='password' type='password'></div>
		<div><button type='submit'>登陆</button></div>
	</form>
<?php } ?>
</body>
</html>
