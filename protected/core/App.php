<?php
	/**
	* 
	*/
	class App
	{
		protected static $instance;
		public static $dbHandler;

		//Получение единственного экземпляра класса App
		public static function getInstance($connectParams=array())
		{
			if (self::$instance==null) {
				self::$instance = new self($connectParams);
			}
			return self::$instance;
		}

		protected function __construct($connectParams)
		{
			$host = (isset($connectParams['host']) ? $connectParams['host'] : 'localhost');
			$dbname = (isset($connectParams['dbname']) ? $connectParams['dbname'] : '');
			$user = (isset($connectParams['user']) ? $connectParams['user'] : 'root');
			$pass = (isset($connectParams['pass']) ? $connectParams['pass'] : '');

			try {  
				$dbHandler = new PDO("mysql:host=".$host.";dbname=".$dbname, $user, $pass);
				$dbHandler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$dbHandler->exec("set names utf8");
			}  
			catch(PDOException $e) {  
    			echo $e->getMessage(); 
			}
			self::$dbHandler = $dbHandler;
		}

		private function __clone()
		{
		}

		private function __wakeup()
		{
		}

		//Парсинг пути
		public static function route()
		{
			$controllerName = 'Site';
			$actionName = 'page';
			$params = array();

			$routeArr = explode('/', $_SERVER['REQUEST_URI']);
			if (!empty($routeArr[1])) {
				$controllerName = ucfirst($routeArr[1]);
			}
			if (!empty($routeArr[2])) {
				$end = strpos($routeArr[2], '?');
				if ($end>0) {
					$actionName = substr($routeArr[2], 0, $end);
				}
				else{
					$actionName = $routeArr[2];
				}
			}

			if (count($routeArr)>3) { //Если кроме "/контроллера/экшна" в пути есть что-то еще - передаю это в массив параметров
				for ($i=3; $i<count($routeArr); $i++) { 
					$end = strpos($routeArr[$i], '?');
					if ($end>0) {
						$params[] = substr($routeArr[$i], 0, $end);
					}
					else{
						$params[] = $routeArr[$i];
					}
				}
			}
			if (!empty($_REQUEST)) {
				foreach ($_REQUEST as $key => $value) {
					$params[$key] = $value;
				}
			}

			$controller = new $controllerName;
			$action = $actionName."Action";
			$controller->$action($params);
		}
	}
?>