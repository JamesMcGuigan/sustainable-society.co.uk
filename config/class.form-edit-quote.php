<?php

require_once('inc/class.form.php');
include_once('config/sql.listing.php');
require_once('class.form-subform-keywords.php');

class form_edit_quote extends form {
	// declare primary_field, primary_field_value, table, and spec
	function init_variables() {
		/* STOP*/
		$this->primary_field = 'id';
		$this->table = 'quotes';

		$this->specs =array(
			array('field' => 'id',
						'type'  => 'fixed',
						'title' => 'ID',
						'is_primary'=>true ),

      array('field' => 'quote',
            'type'=>'textarea',
            'title'=>'Quote',
            'rows'=>20,
           ),

      array('field' => 'source',
            'type'=>'text',
            'title'=>'From',
           ),

      array('field' => 'author',
            'type'=>'text',
            'title'=>'By',
           ),

			array('field'      => '',
 						'title'      => 'Keywords',
			      'type'       => 'subform',
						'subform'    => new form_subform_keywords($this) ),


			array('field' => 'provisional',
						'type'=>'checkbox',
						'title'=>'Provisional Entry',
						'comments'=>'Uncheck to approve user submitted links and add to public listing', 'admin_only' => true),

			array('field' => 'deleted',
						'type'=>'checkbox',
						'title'=>'Deleted Entry',
						'comments'=>'Check to remove from the public listing',
						'admin_only' => true),

			array('field' => 'created',
						'type'=>'created',
						'title'=>'Entry Created',
						'table'=>'links',
						'admin_only' => true ),

			array('field' => 'modified',
						'type'=>'modified',
						'title'=>'Entry Modified',
						'table'=>'links',
						'admin_only' => true ),

      array('field' => 'contact',
						'type'=>'text',
						'title'=>'Contact Email',
						'after_buttons'=>true,
						'validate_as'=>'email'),

			array('field' => 'notes',
						'type'=>'textarea',
						'title'=>'Webmaster\'s Notes',
						'comments'=>'Note: These notes will not be displayed in the listing',
						'after_buttons'=>true),
		);

		parent::init_variables();
	}

	function print_example() {
    $hide_fields = array('provisional','deleted');
    $function = 'quote_SQL_HTML_by_id';
    $id = $this->primary['quotes.id'];

    parent::print_example();
    parent::print_example_auto($hide_fields, $function, $id);
  }
}

?>
