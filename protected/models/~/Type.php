<?php
	/**
	* 
	*/
	class Type extends Model
	{
		protected function tableName()
		{
			return 'type';
		}

		protected function tableFields()
		{
			return array(
				'id'=>null,
				'name'=>null
				);
		}

		protected function tableRelations()
		{
			return array(
				'properties' => array('MANY_MANY', 'properties', array('prop2type', 'fk_type', 'fk_prop'))
				//название свойства => (тип связи, таблица, свойство для связи или массив (таблица-связка, поле с ключом текущей таблицы, поле с ключом второй таблицы))
				);
		}

		protected function tableKey()
		{
			return 'id';
		}
	}
?>