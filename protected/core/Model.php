<?php
	/**
	* 
	*/
	abstract class Model
	{
		protected $tableName;
		protected $tableFields;
		protected $tableRelations;
		protected $tableKey;

		abstract protected function tableName();
		abstract protected function tableFields();
		abstract protected function tableRelations();
		abstract protected function tableKey();

		public final function __construct()
		{
			$this->tableName = $this->tableName();
			$this->tableFields = $this->tableFields();
			$this->tableRelations = $this->tableRelations();
			$this->tableKey = $this->tableKey();
		}

		public final function __get($name)
		{
			switch ($name) {
				case 'tableName':
					return $this->tableName;
					break;
				case 'tableFields':
					return $this->tableFields;
					break;
				case 'tableRelations':
					return $this->tableRelations;
					break;
				case 'tableKey':
					return $this->tableKey;
					break;
				case 'id':
					return $this->tableFields[$this->tableKey];
					break;
				case isset($this->tableRelations[$name]):
					if ($this->tableRelations[$name][0]=='BELONGS_TO') {
						$linkedName = ucfirst($this->tableRelations[$name][1]);
						$linkedObj = new $linkedName;
						$linkedRes = $linkedObj->findById($this->tableFields[$this->tableRelations[$name][2]]);
						return $linkedRes;
					}
					elseif ($this->tableRelations[$name][0]=='HAS_MANY') {
						$linkedName = ucfirst($this->tableRelations[$name][1]);
						$linkedObj = new $linkedName;
						$criteria = new Criteria($linkedObj);
						$criteria->condition(array($this->tableRelations[$name][2]=>array($this->id)));
						$linkedRes = $linkedObj->findByCriteria($criteria);
						return $linkedRes;
					}
					elseif ($this->tableRelations[$name][0]=='MANY_MANY') {
						return 'MANY_MANY'; //РЕАЛИЗОВАТЬ!!!
					}
					break;
				case isset($this->tableFields[$name]):
					return $this->tableFields[$name];
					break;
			}
		}

		public final function __set($name, $value)
		{
			switch ($name) {
				case array_key_exists($name, $this->tableFields):
					$this->tableFields[$name] = $value;
					break;
				case (array_key_exists($name, $this->tableRelations) && is_array($value)):
					if ($this->tableRelations[$name][0]=='BELONGS_TO') {
						$linkedName = ucfirst($this->tableRelations[$name][1]);
						$linkedObj = new $linkedName;

						//Проверка на уникальность (ДОБАВИТЬ ПАРАМЕТР В МОДЕЛИ ДЛЯ ОПРЕДЕЛЕНИЯ ПОЛЯ, КАК УНИКАЛЬНОГО!)
						foreach ($value as $key => $val) {
							if (array_key_exists($key, $linkedObj->tableFields)) {
								$linkedObj->tableFields[$key] = $val;
								$criteria = new Criteria($linkedObj);
								$criteria->condition(array($key=>array($val)));
							}
						}
						$test = $linkedObj->findByCriteria($criteria);
						if (empty($test)) {
							$lastId = $linkedObj->save();
						}
						else{
							foreach ($test as $key => $value) {
								$lastId = $key;
							}	
						}
						///////////////////////////

						$this->tableFields[$this->tableRelations[$name][2]] = $lastId;
					}
					break;
			}
		}

		private function isEmpty()
		{
			foreach ($this->tableFields as $field) {
				if (!empty($field)) {
					$isNotEmpty[] = 1;
				}
				else {
					$isNotEmpty[] = 0;
				}
			}
			if (in_array('1', $isNotEmpty)) {
				return false;
			}
			else {
				return true;
			}
		}

		private function getResultObjects($result)
		{
			if (!empty($this->tableFields)) { //Определяются в конструкторе родительского класса
				if (!empty($result)) {
					foreach ($result as $row) {
						$modelObj = new $this->tableName;
						foreach ($modelObj->tableFields as $mkey => $mvalue) {
							foreach ($row as $rkey => $rvalue) {
								if ($modelObj->tableName.'_'.$mkey==$rkey) {
									$modelObj->tableFields[$mkey] = $rvalue;
								}
							}
						}
						foreach ($modelObj->tableRelations as $key => $value) {
							$tmp = ucfirst($value[1]);
							$relObject = new $tmp;
							foreach ($relObject->tableFields as $rokey => $rovalue) {
								foreach ($row as $rkey => $rvalue) {
									if ($relObject->tableName.'_'.$rokey==$rkey) {
										$relObject->tableFields[$rokey] = $rvalue;
									}
								}
							}
							if (!$relObject->isEmpty()) {
								$modelObj->tableFields[$key] = $relObject;
							}
							else{
								$modelObj->tableFields[$key] = null;
							}
						}
						$modelArr[] = $modelObj;
					}

					//Устраняю повторение основных объектов (HAS_MANY, MANY_MANY) при выводе подобъектов
					foreach ($modelArr as $model) {
						foreach ($model->tableRelations as $relName => $relValue) {
							$subObjArr[$model->id][] = $model->tableFields[$relName];
						}
						$parentModelArr[$model->id] = $model;
					}

					foreach ($subObjArr as $sKey => $sValue) {
						foreach ($parentModelArr as $pKey => $pValue) {
							if ($pKey==$sKey) {
								foreach ($pValue->tableRelations as $relName => $relValue) {
									$pValue->tableFields[$relName] = $sValue;
								}
								$finalArr[$pKey] = $pValue;
							}
						}
					}
					////////////////////////////////////////////////////////////////////////////
				}
			}
			return $finalArr;
		}

		public final function findById($id=null)
		{
			$data = array();
			if ($id==intval($id) && isset(App::$dbHandler)) {
				$criteria = new Criteria($this->tableName);
				$criteria->condition(array($this->tableKey=>array($id)));
				$result = $criteria->execute();
			}
			if (!empty($result)) {
				$data = $this->getResultObjects($result);
			}

	/*echo "<pre>";
	print_r($criteria);
	echo "</pre><hr>";*/

			return $data;
		}

		public final function findByParams($params=array(), $isStrict=true, $comparison='=')
		{
			$data = array();
			if (!empty($params) && isset(App::$dbHandler)) {
				$criteria = new Criteria($this->tableName, 'SELECT', $isStrict);
				$criteria->condition($params);
				$result = $criteria->execute();
			}
			if (!empty($result)) {
				$data = $this->getResultObjects($result);
			}

	/*echo "<pre>";
	print_r($criteria);
	echo "</pre><hr>";*/

			return $data;
		}

		public final function findByCriteria($criteria)
		{
			$data = array();
			if (is_object($criteria)) {
				$result = $criteria->execute();
			}
			if (!empty($result)) {
				$data = $this->getResultObjects($result);
			}

	/*echo "<pre>";
	print_r($criteria);
	echo "</pre><hr>";*/

	/*echo "<pre>";
	print_r($result);
	echo "</pre><hr>";*/

			return $data;
		}

		//Улучшить!
		public final function findBySql($query) //НЕ ВОЗВРАЩАЕТ МАССИВ ОБЪЕКТОВ! ВОЗВРАЩАЕТ АССОЦИАТИВНЫЙ МАССИВ!
		{
			$data = array();
			if (empty($query)) return $data;
				
			$queryObj = App::$dbHandler->prepare($query);
			$result = $queryObj->execute();
			$data = $queryObj->fetchAll(PDO::FETCH_ASSOC);

			return $data;
		}

		//Проверить!
		public final function count($criteria)
		{
			$data = array();
			if (is_object($criteria)) {
				$query = "SELECT COUNT(*) as countNum FROM ".$criteria->table." ".$criteria->condition;
				$queryObj = App::$dbHandler->prepare($query);
				$result = $queryObj->execute();
			}
			if (!empty($result)) {
				$result = $queryObj->fetchAll(PDO::FETCH_ASSOC);
			}

			return $result;
		}

		public final function save()
		{
			$criteria = new Criteria($this, 'SAVE');
			$criteria->condition(array($this->tableKey=>array($this->tableFields[$this->tableKey])));
			$result = $criteria->execute();

	/*echo "<pre>";
	print_r($criteria);
	echo "</pre><hr>";*/

			return $result;
		}
	}
?>