<?php
	ini_set('display_errors', 1);
	error_reporting(E_ALL);

	require_once __DIR__.'/protected/init.php';

	//Старт приложения с параметрами для соединения с БД (синглтон)
	App::getInstance($connectParams);
	App::route();
?>