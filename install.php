<?php

//if (!file_exists('install')) die('没有 install 文件,无法安装,请在根目录下创建空 install 文件.');

//if (!unlink('install')) die('install 文件删除失败');

$config = require 'dataBaseConfig.include.php';

$config['host'] = getenv('HTTP_BAE_ENV_ADDR_SQL_IP');
$config['port'] = getenv('HTTP_BAE_ENV_ADDR_SQL_PORT');
$config['user'] = getenv('HTTP_BAE_ENV_AK');
$config['password'] = getenv('HTTP_BAE_ENV_SK');

$con = mysql_connect($config['host'] . ':' . $config['port'],$config['user'],$config['password']) or die('db connect error');

//mysql_query('drop database ' . $config['dbName'],$con);

//mysql_query('CREATE DATABASE ' . $config['dbName'],$con) or die('db create error');

mysql_select_db($config['dbName'],$con) or die('select db error');

mysql_query('drop table user',$con);// or die('drop table user error');

mysql_query('create table user(id int primary key auto_increment,
								name varchar(20) not null default "",
								password varchar(64) not null default "",
								cookie varchar(1024) not null default "",
								last date not null default "1111-11-11")
								char set utf8 engine innodb',$con) or die('create table user error');

mysql_query('alter table user add unique(`name`)',$con) or die('add unique to user error');

mysql_query('drop table bars',$con);// or die('drop table bars error');

mysql_query('create table bars(id int primary key auto_increment,
								user int not null default 0,
								bar varchar(200) not null default "",
								time date not null default "1111-11-11",
								exp int not null default "0")
								char set utf8 engine innodb',$con) or die('create table bars error');

mysql_close($con);


?>
