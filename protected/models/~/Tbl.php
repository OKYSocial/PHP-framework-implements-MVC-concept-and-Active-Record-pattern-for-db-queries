<?php
	/**
	* 
	*/
	class Tbl extends Model
	{
		protected function tableName()
		{
			return 'tbl';
		}

		protected function tableFields()
		{
			return array(
				'id'=>null,
				'name'=>null,
				'text'=>null,
				'fk_tbl2'=>null
				);
		}

		protected function tableRelations()
		{
			return array(
				'table2' => array('BELONGS_TO', 'tbl2', 'fk_tbl2')
				);
		}

		protected function tableKey()
		{
			return 'id';
		}
	}
?>