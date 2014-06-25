<?php
	/**
	* 
	*/
	class Autos extends Model
	{
		protected function tableName()
		{
			return 'autos';
		}

		protected function tableFields()
		{
			return array(
				'id'=>null,
				'auto'=>null,
				);
		}

		protected function tableRelations()
		{
			return array(
				//название свойства => (тип связи, таблица, свойство для связи или массив (таблица-связка, поле с ключом текущей таблицы, поле с ключом второй таблицы))
				'articles' => array('HAS_MANY', 'articles', 'fk_auto')
				);
		}

		protected function tableKey()
		{
			return 'id';
		}

		public static function getAll()
		{
			$data = array();

			$model = new Autos;
			$criteria = new Criteria($model);
			$data = $model->findByCriteria($criteria);

			return $data;
		}
	}
?>