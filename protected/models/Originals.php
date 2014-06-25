<?php
	/**
	* 
	*/
	class Originals extends Model
	{
		protected function tableName()
		{
			return 'originals';
		}

		protected function tableFields()
		{
			return array(
				'id'=>null,
				'original'=>null,
				);
		}

		protected function tableRelations()
		{
			return array(
				//название свойства => (тип связи, таблица, свойство для связи или массив (таблица-связка, поле с ключом текущей таблицы, поле с ключом второй таблицы))
				'articles' => array('HAS_MANY', 'articles', 'fk_original')
				);
		}

		protected function tableKey()
		{
			return 'id';
		}
	}
?>