<?php
	/**
	* 
	*/
	class Criteria
	{
		protected $isStrict; // true/false - AND/OR
		protected $queryType; //SELECT/INSERT/UPDATE
		protected $query;
		protected $table;
		protected $currentModel; //Текущая модель (используется для метода save() класса Model)
		protected $reqFields; //array('id','name','text')
		protected $condition; //array('name'=>array('TEST','LIKE')
		protected $addCondition; //'GROUP BY tbl2.id'/'ORDER BY id DESC'
		protected $leftJoins;
		protected $innerJoins;

		public $limit;
		public $offset;

		public function __construct($model, $queryType='SELECT', $isStrict=true, $reqFields=array())
		{
			if (is_object($model)) { //На всякий случай
				$this->table = $model->tableName;
				$this->currentModel = $model;
			}
			else {
				$this->table = $model;
				$this->currentModel = new $model; //Не уверен, что это нужно
			}
			$this->queryType = $queryType;
			$this->isStrict = $isStrict;
			foreach ($reqFields as $field) {
				$end = strpos($field, '.');
				if ($end===false) {
					$this->reqFields[] = $this->table.'.'.$field.' AS '.$this->table.'_'.$field;
				}
				else{
					$preffix = substr($field, 0, $end);
					$suffix = substr($field, $end+1, strlen($field));
					$this->reqFields[] = $field.' AS '.$preffix.'_'.$suffix;
				}
			}
		}

		public function __get($name)
		{
			return $this->$name;
		}

		public function condition($params)
		{
			if (!empty($params)) {
				foreach ($params as $key => $value) {
					$end = strpos($key, '.');
					if ($end===false) {
						$queryParams[$this->table.'.'.$key] = $value;
					}
					else{
						$queryParams[$key] = $value;
					}
				}
				reset($queryParams); //Получаю первый элемент ассоциативного массива
				$firstKey = key($queryParams);
				$this->condition = ' WHERE '.$firstKey.(isset($queryParams[$firstKey][1]) ? ' '.$queryParams[$firstKey][1].' ' : '=').'\''.$queryParams[$firstKey][0].'\'';
				unset($queryParams[$firstKey]); //Удаляю первый элемент массива параметров, чтобы он не повторялся в запросе
				foreach ($queryParams as $pkey => $pvalue) {
					$this->condition .= ' '.($this->isStrict ? 'AND ' : 'OR ').$pkey.(isset($pvalue[1]) ? ' '.$pvalue[1].' ' : '=\'').$pvalue[0].'\'';
				}
			}
		}

		public function addCondition($str='')
		{
			$this->addCondition = $str;
		}

		public function leftJoins($relations=array())
		{
			if (!empty($relations)) {
				foreach ($relations as $key => $value) {
					$this->leftJoins[$key] = array($value[0], ucfirst($value[1]), $value[2]);
				}
			}
		}

		public function innerJoins($relations=array())
		{
			if (!empty($relations)) {
				foreach ($relations as $key => $value) {
					$this->innerJoins[$key] = array($value[0], ucfirst($value[1]), $value[2]);
				}
			}
		}

		private function buildJoins($relationArr, $relationType, $joinedQuery)
		{
			if (isset($relationArr[1])) {
				$joinedModel = new $relationArr[1]();
				if (empty($this->reqFields)) {
					foreach ($joinedModel->tableFields as $fieldName => $fieldValue) {
						$this->query .= ', '.$joinedModel->tableName.'.'.$fieldName.' AS '.$joinedModel->tableName.'_'.$fieldName;
					}
				}
			}
			if ($relationArr[0]=='BELONGS_TO') {
				$joinedQuery .= ' '.$relationType.' JOIN '.$joinedModel->tableName;
				$joinedQuery .= ' ON '.$this->table.'.'.$relationArr[2].'='.$joinedModel->tableName.'.'.$joinedModel->tableKey;
			}
			if ($relationArr[0]=='HAS_MANY') {
				$joinedQuery .= ' '.$relationType.' JOIN '.$joinedModel->tableName;
				$joinedQuery .= ' ON '.$this->table.'.'.$this->currentModel->tableKey.'='.$joinedModel->tableName.'.'.$relationArr[2];
			}
			if ($relationArr[0]=='MANY_MANY') {
				if (is_array($relationArr[2])) {
					$bundleTable = ucfirst($relationArr[2][0]); //Таблица-связка
					$currentTableKey = $relationArr[2][1]; //Ключ в таблице-связке на текущую таблицу
					$joinedTableKey = $relationArr[2][2]; //Ключ в таблице-связке на вторую таблицу

					$bundleModel = new $bundleTable();
					$joinedQuery .= ' '.$relationType.' JOIN '.$bundleModel->tableName;
					$joinedQuery .= ' ON '.$this->table.'.'.$this->currentModel->tableKey.'='.$bundleModel->tableName.'.'.$currentTableKey;
					$joinedQuery .= ' '.$relationType.' JOIN '.$joinedModel->tableName;
					$joinedQuery .= ' ON '.$joinedModel->tableName.'.'.$joinedModel->tableKey.'='.$bundleModel->tableName.'.'.$joinedTableKey;	
				}
			}
			return $joinedQuery;
		}

		private function buildQuery()
		{
			if ($this->queryType=='SELECT') {
				$this->query = $this->queryType;
				if (!empty($this->reqFields)) {
					if (!in_array($this->currentModel->tableKey, $this->reqFields)) {
						$this->query .= ' '.$this->table.'.'.$this->currentModel->tableKey.' AS '.$this->table.'_'.$this->currentModel->tableKey.','; //Проверяю, есть ли PK в запрашиваемых полях и, если его нет, принудительно тащу его, чтобы не было путаницы с родительскими объектами, если их возвращается несколько
					}
					$i=1;
					foreach ($this->reqFields as $reqField) {
						if ($i<count($this->reqFields)) {
							$this->query .= ' '.$reqField.',';
						}
						else{
							$this->query .= ' '.$reqField;
						}
						$i++;
					}
				}
				else {
					$i=1;
					foreach ($this->currentModel->tableFields as $key => $value) {
						if ($i<count($this->currentModel->tableFields)) {
							$this->query .= ' '.$this->table.'.'.$key.' AS '.$this->table.'_'.$key.',';
						}
						else{
							$this->query .= ' '.$this->table.'.'.$key.' AS '.$this->table.'_'.$key;
						}
						$i++;
					}
				}

				//JOIN'ы
				$joinedQuery = '';
				if (!empty($this->leftJoins)) {
					foreach ($this->leftJoins as $value) {
						$joinedQuery = $this->buildJoins($value, 'LEFT', $joinedQuery);
					}
				}
				if (!empty($this->innerJoins)) {
					foreach ($this->innerJoins as $value) {
						$joinedQuery = $this->buildJoins($value, 'INNER', $joinedQuery);
					}
				}

				$this->query .= ' FROM '.$this->table;
				$this->query .= $joinedQuery;
				$this->query .= $this->condition;
				$this->query .= ' '.$this->addCondition;
				if (isset($this->limit) && isset($this->offset)) {
					$this->query .= ' LIMIT '.$this->offset.','.$this->limit;
				}
			}
			elseif ($this->queryType=='SAVE') {

				//Избавляюсь от свойств объекта определенных связанными таблицами, чтобы они не участвовали в INSERT/UPDATE
				foreach ($this->currentModel->tableRelations as $relName => $relValue) {
					$relations[] = $relName;
				}
				foreach ($this->currentModel->tableFields as $field => $value) {
					if (!in_array($field, $relations)) {
						$originalFields[$field] = $value;
					}
				}

				if ($this->currentModel->tableFields[$this->currentModel->tableKey]) {
					$this->query = 'UPDATE '.$this->table.' SET';
					$i=1;
					foreach ($originalFields as $key => $value) {
						if ($i<count($originalFields)) {
							$this->query .= ' '.$key.'=\''.$value.'\',';
						}
						else{
							$this->query .= ' '.$key.'=\''.$value.'\'';
						}
						$i++;
					}
					$this->query .= $this->condition;
					$this->query .= ' '.$this->addCondition; //ПРОВЕРИТЬ!
				}
				else{
					$this->query = 'INSERT INTO '.$this->table.' (';
					foreach ($originalFields as $key => $value) {
						if ($key!=$this->currentModel->tableKey && !empty($value)) {
							$fieldsArr[] = $key;
							$valuesArr[] = $value;
						}
					}
					$i=1;
					foreach ($fieldsArr as $field) {
						if ($i<count($fieldsArr)) {
							$this->query .= ' `'.$field.'`,';
						}
						else{
							$this->query .= ' `'.$field.'`';
						}
						$i++;
					}
					$this->query .= ') VALUES (';
					$i=1;
					foreach ($valuesArr as $value) {
						if ($i<count($valuesArr)) {
							$this->query .= ' \''.$value.'\',';
						}
						else{
							$this->query .= ' \''.$value.'\'';
						}
						$i++;
					}
					$this->query .= ')';
				}
			}
		}

		public function execute()
		{
			$this->buildQuery();
	
//var_dump($this->query);

			$queryObj = App::$dbHandler->prepare($this->query);
			$result = $queryObj->execute();

			if ($this->queryType=='SELECT') {
				$result = $queryObj->fetchAll(PDO::FETCH_ASSOC);
			}
			elseif ($this->queryType=='SAVE') {
				$result = App::$dbHandler->lastInsertId();
			}

			return $result;
		}
	}
?>