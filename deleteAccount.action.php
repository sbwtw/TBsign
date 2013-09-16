<?php

if (isset($_POST['userName']) && isset($_POST['password'])){
	
	require_once 'dataBase.class.php';

	$mysqli = new DataBase();
	$mysqli->table('user');
	$mysqli->where('name = \'' . $mysqli->escape($_POST['userName']) . '\' and password = \'' . md5($_POST['userName'] . md5($_POST['password'])) . '\'');
	$result = $mysqli->select();

	if (isset($result[0]) && $result[0]['name'] == $_POST['userName']){
		$mysqli->where('name = \'' . $_POST['userName'] . '\'');
		$mysqli->delete() && die('delete successful!');
	}
}

die('delete error!');

?>
