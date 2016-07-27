<?php

require_once('inc/class.form.php');
require_once('class.form-subform-keywords.php');
include_once('config/sql.listing.php');


class form_edit_homepage extends form {
  // declare primary_field, primary_field_value, table, and spec
  function init_variables() {
    $this->table = 'homepage';

    $this->specs =array(
      array('field'      => 'id',
            'type'       => 'fixed',
            'title'      => 'ID',
            'is_primary' =>  true ),

      array('field'      => 'type',
            'type'       => 'text',
            'title'      => 'Type',
            ),

      array('field'      => 'title',
            'type'       => 'text',
            'title'      => 'Title',
            'unique'     =>  true,
            ),

      array('field'      => 'by',
            'type'       => 'text',
            'title'      => 'By',
            ),

      array('field'      => 'text',
            'type'       => 'textarea',
            'title'      => 'Text',
            'rows'       =>  20,
            'required'   =>  true,
           ),

      array('field'      => 'linktext',
            'type'       => 'text',
            'title'      => 'Link Text',
            'unique'     =>  true,
            ),

      array('field'      => 'link',
            'type'       => 'text',
            'title'      => 'Link URL',
            'validate_as'=> 'link',
           ),

      array('field'      => 'position',
            'type'       => 'number',
            'title'      => 'Position',
						'required'   =>  true,
           ),

      array('field'      => 'width',
            'type'       => 'text',
            'title'      => 'Cell Width',
           ),

      array('field'      => 'side',
            'type'       => 'combo',
            'title'      => 'Page Side',
            'defaults'   => array('wide','left','right'),
           ),

      array('field'      => 'border',
            'type'       => 'checkbox',
            'title'      => 'Border',
           ),

      array('field'      => 'style',
            'type'       => 'text',
            'title'      => 'CSS Styles',
            'admin_only' =>  true
           ),

      array('field'      => 'class',
            'type'       => 'text',
            'title'      => 'CSS Class',
            'admin_only' =>  true
           ),

      array('field'      => 'hidden',
            'type'       => 'checkbox',
            'title'      => 'Hide Entry',
            'comments'   => 'Will remove entry from display',
            'admin_only' =>  true
           ),

      array('field'      => 'hidebefore',
            'type'       => 'datetime',
            'title'      => 'Hide Before Date',
            'admin_only' =>  true
           ),

      array('field'      => 'hideafter',
            'type'       => 'datetime',
            'title'      => 'Hide After Date',
            'admin_only' =>  true
           ),


      array('field'      => 'provisional',
            'type'       => 'checkbox',
            'title'      => 'Provisional Entry',
            'comments'   => 'Uncheck to approve user submitted links and add to public listing',
            'admin_only' =>  true ),

      array('field'      => 'deleted',
            'type'       => 'checkbox',
            'title'      => 'Deleted Entry',
            'comments'   => 'Check to remove from the public listing',
            'admin_only' =>  true ),

      array('field'      => 'created',
            'type'       => 'created',
            'title'      => 'Entry Created',
            'table'      => 'links',
            'admin_only' =>  true ),

      array('field'      => 'modified',
            'type'       => 'modified',
            'title'      => 'Entry Modified',
            'table'      => 'links',
            'admin_only' =>  true ),

      array('field'      => 'contact',
            'type'       => 'text',
            'title'      => 'Contact Email',
            'after_buttons'=>  true,
            'validate_as'=> 'email' ),

      array('field'      => 'notes',
            'type'       => 'textarea',
            'title'      => 'Webmaster\'s Notes',
            'comments'   => 'Note: These notes will not be displayed in the listing',
            'after_buttons'=>  true ),
    );

    parent::init_variables();
  }

  function print_example() {
    $hide_fields = array('hidden','provisional','deleted');
    $function = 'homepage_SQL_HTML_by_id';
    $id = $this->primary['homepage.id'];

    parent::print_example();
    parent::print_example_auto($hide_fields, $function, $id);
  }
}

?>
