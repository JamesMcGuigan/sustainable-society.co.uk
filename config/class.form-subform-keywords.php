<?php

require_once('inc/class.form.php');

class form_subform_keywords extends form_subform {

	function init_variables() {
		global $admin_mode;
		$combo_type =  ($admin_mode) ? 'combo_alt' : 'combo';

		$this->specs = array(
			array('field'      => 'keyword',
 						'type'       => $combo_type,
						'combo_cat_field'=>'keywordtype',
						'combo_cat_table'=>'keywordtypes',
						'required'   => true,
						'defaults'   => array(''),
 					),
 			array('field'      => 'idtype',
 						'type'       => 'hidden',
 						'is_primary' => true,
						'primary_source' => 'PARENT_TABLE',
						'unrequired' => true,
 					),
 			array('field'      => 'id',
 						'type'       => 'hidden',
 						'is_primary' => true,
						'primary_source' => 'GET',
 					),
		);

		$this->table         = 'keywords';
		parent::init_variables();
	}
}

?>