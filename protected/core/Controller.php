<?php
	/**
	* 
	*/
	class Controller
	{
		//Обработка вызова несуществующих методов
		public function __call($name, $params=array())
		{
			try 
			{
				throw new Exception("<p>Вызов несуществующего метода</p>");
			} 
			catch (Exception $e) 
			{
				echo $e->getMessage();
			}
		}

		//Обработка вызова несуществующих статических методов
		public static function __callStatic($name, $params=array())
		{
			try 
			{
				throw new Exception("<p>Вызов несуществующего метода</p>");
			} 
			catch (Exception $e) 
			{
				echo $e->getMessage();
			}
		}

		//Обработка вызова несуществующих/недоступных свойств
		public function __get($name)
		{

		}

		//Обработка перенаправления
		protected function redirect($path)
		{
			header("Location:".$path);
		}
	}
?>