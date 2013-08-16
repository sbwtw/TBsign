<?php
$logList = null;

if (isset($_POST['userName']) && isset($_POST['password'])){
	require_once 'dataBase.class.php';

	$mysqli = new DataBase();
	$mysqli->table('user,bars');
	$mysqli->fields('bars.bar as bar, bars.exp as exp');
	$mysqli->where('user.id = bars.user and bars.time = \'' . date('Y-m-d') . '\' and user.name = \'' . $mysqli->escape($_POST['userName']) . '\' and password = \'' . md5($_POST['userName'] . md5($_POST['password'])) . '\'');
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
	echo '<table style="text-align:center;margin:0 auto;">';
	echo '<tr><th>吧名</th><th>节操</th></tr>';
	foreach ($logList as $i){
		echo '<tr><td><a target="_blank" href=http://tieba.baidu.com/f?kw=' . $i['bar'] . '>' . iconv('gbk','utf-8',urldecode($i['bar'])) . '</a></td><td>' . $i['exp'] . '</td></tr>';
	}
	echo '</table>';
	echo '<div>注:<br>节操0表示还没有执行签到<br>节操为-1表示已经签过或不能签到<br>节操为-2表示签到失败<br>节操为-4表示失败后补签失败</div>';
} else { ?>
	<form action='#' method='post' target='_self'>
		<div><span>百度帐号:</span><input name='userName' type='text'></div>
		<div><span>管理密码:</span><input name='password' type='password'></div>
		<div><button type='submit'>登陆</button></div>
	</div>
<?php } ?>
</body>
</html>
