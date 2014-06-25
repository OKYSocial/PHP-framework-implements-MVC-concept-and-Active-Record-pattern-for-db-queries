<?php
	/**
	* 
	*/
	class Prop2type extends Model
	{
		protected function tableName()
		{
			return 'prop2type';
		}

		protected function tableFields()
		{
			return array(
				'fk_prop'=>null,
				'fk_type'=>null
				);
		}

		protected function tableRelations()
		{
			return array(
				'types' => array('BELONGS_TO', 'type', 'fk_type'),
				'properties' => array('BELONGS_TO', 'properties', 'fk_prop')
				);
		}

		protected function tableKey()
		{
			return array('fk_prop', 'fk_type'); //ВОЗМОЖНЫ ОШИБКИ С СОСТАВНЫМИ КЛЮЧАМИ (особенно в методе save())
		}
	}
?>