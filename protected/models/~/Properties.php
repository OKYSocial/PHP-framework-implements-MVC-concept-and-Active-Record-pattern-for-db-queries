<?php
	/**
	* 
	*/
	class Properties extends Model
	{
		protected function tableName()
		{
			return 'properties';
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
				'types' => array('MANY_MANY', 'type', array('prop2type', 'fk_prop', 'fk_type'))
				);
		}

		protected function tableKey()
		{
			return 'id';
		}
	}
?>