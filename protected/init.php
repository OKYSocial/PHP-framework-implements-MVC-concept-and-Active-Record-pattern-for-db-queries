<?php
	require_once __DIR__.'/config.php';

	//Автозагрузка классов
	spl_autoload_register('autoload');
	function autoload($className)
	{
		$classFile = false;
		$fileName = $className.".php";
		$paths = array(
			CORE_DIR,
			MODEL_DIR,
			CONTROLLER_DIR,
			VIEW_DIR,
			);

		foreach ($paths as $path) {
			if (file_exists($path.$fileName)) {
				$classFile = $path.$fileName;
				break;
			}
		}

		if ($classFile!==false) {
			include $classFile;
		}
		else {
			die('<p>Файл для класса '.$className.' не найден!</p>');
		}
	}
?>