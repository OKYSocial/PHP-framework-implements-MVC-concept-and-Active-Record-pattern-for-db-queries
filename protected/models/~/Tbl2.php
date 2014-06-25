<?php
	/**
	* 
	*/
	class Tbl2 extends Model
	{
		protected function tableName()
		{
			return 'tbl2';
		}

		protected function tableFields()
		{
			return array(
				'id'=>null,
				'some_field'=>null
				);
		}

		protected function tableRelations()
		{
			return array(
				'tables' => array('HAS_MANY', 'tbl', 'fk_tbl2')
				);
		}

		protected function tableKey()
		{
			return 'id';
		}
	}
?>