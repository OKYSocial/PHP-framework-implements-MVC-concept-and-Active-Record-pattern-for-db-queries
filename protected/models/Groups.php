<?php
	/**
	* 
	*/
	class Groups extends Model
	{
		protected function tableName()
		{
			return 'groups';
		}

		protected function tableFields()
		{
			return array(
				'id'=>null,
				'group'=>null,
				);
		}

		protected function tableRelations()
		{
			return array(
				//название свойства => (тип связи, таблица, свойство для связи или массив (таблица-связка, поле с ключом текущей таблицы, поле с ключом второй таблицы))
				'articles' => array('HAS_MANY', 'articles', 'fk_group')
				);
		}

		protected function tableKey()
		{
			return 'id';
		}

		public static function getAll()
		{
			$data = array();

			$model = new Groups;
			$criteria = new Criteria($model);
			$data = $model->findByCriteria($criteria);

			return $data;
		}
	}
?>