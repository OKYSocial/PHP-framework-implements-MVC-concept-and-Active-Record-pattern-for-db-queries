<?php
	/**
	* 
	*/
	class Articles extends Model
	{
		protected function tableName()
		{
			return 'articles';
		}

		protected function tableFields()
		{
			return array(
				'id'=>null,
				'art'=>null,
				'number'=>null,
				'description'=>null,
				'fk_auto'=>null,
				'fk_brand'=>null,
				'fk_group'=>null,
				'fk_original'=>null,
				);
		}

		protected function tableRelations()
		{
			return array(
				//название свойства => (тип связи, таблица, свойство для связи или массив (таблица-связка, поле с ключом текущей таблицы, поле с ключом второй таблицы))
				'groups' => array('BELONGS_TO', 'groups', 'fk_group'),
				'autos' => array('BELONGS_TO', 'autos', 'fk_auto'),
				'brands' => array('BELONGS_TO', 'brands', 'fk_brand'),
				'originals' => array('BELONGS_TO', 'originals', 'fk_original'),
				);
		}

		protected function tableKey()
		{
			return 'id';
		}
	}
?>