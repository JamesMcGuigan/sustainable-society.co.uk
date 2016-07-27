<?php

require_once('inc/class.form.php');
include_once('config/sql.listing.php');
require_once('class.form-subform-keywords.php');
include_once('inc/inc.london_underground_station_array.php');

class form_edit_article extends form {
  // declare primary_field, primary_field_value, table, and spec
  function init_variables() {
    /* STOP*/
    $this->primary_field = 'id';
    $this->table = 'articles';

    $this->specs =array(
      array('field'      => 'id',
            'type'       => 'fixed',
            'title'      => 'ID',
            'is_primary' => true ),

      array('field'      => 'title',
            'type'       => 'text',
            'title'      => 'Article Title',
            'required'   =>true ),

      array('field'      => 'by',
            'type'       => 'text',
            'title'      => 'By',
           ),

      array('field'      => 'source',
            'type'       => 'text',
            'title'      => 'Source',
            'comments'   => 'If given it will be used as the link text for the link ' ),

      array('field'      => 'link',
            'type'       => 'text',
            'title'      => 'Article Link',
            'validate_as'=> 'link' ),

      array('field'      => 'sort_by',
            'type'       => 'text',
            'title'      => 'Sort By',
            ),

      array('field'      => 'date',
            'type'       => 'date',
            'title'      => 'Date',
            ),

      array('field'     => 'summary',
            'type'      => 'textarea',
            'title'     => 'Summary',
            'comments'  => 'If not given a snipit from the Article Text will be used',
           ),

      array('field'     => 'article_text',
            'type'      => 'textarea',
            'title'     => 'Article Text',
            'rows'      => '20',
            'comments'  => 'Note: Use HTML code'
           ),

      array('field'      => '',
            'type'       => 'subform',
            'title'      => 'Categories',
            'subform'    => new form_subform_keywords($this) ),

      array('field'      => 'provisional',
            'type'       => 'checkbox',
            'title'      => 'Provisional Entry',
            'comments'   => 'Uncheck to approve user submitted links and add to public listing',
            'admin_only' => true),

      array('field'      => 'deleted',
            'type'       => 'checkbox',
            'title'      => 'Deleted Entry',
            'comments'   => 'Check to remove from the public listing',
            'admin_only' => true),

      array('field'      => 'created',
            'type'       => 'created',
            'title'      => 'Entry Created',
            'table'      => 'links',
            'admin_only' => true ),

      array('field'      => 'modified',
            'type'       => 'modified',
            'title'      => 'Entry Modified',
            'table'      => 'links',
            'admin_only' => true ),

      array('field'         => 'contact',
            'type'          => 'text',
            'title'         => 'Contact Email',
            'after_buttons' => true,
            'validate_as'   => 'email'),

      array('field'     => 'private_notes',
            'type'      => 'textarea',
            'title'     => 'Webmaster\'s Notes',
            'comments'  => 'Note: These notes will not be displayed in the listing',
            'after_buttons'=>true),
    );
    parent::init_variables();
  }

  function validate_form() {
    // this function should be overridden to check the user ID is valid
    parent::validate_form();
  }

  function make_suggestions() {
    parent::make_suggestions();

    // check that the link is valid
    if($this->form_data['link'] != '') { // don't bother validing a empty link
      $link = $this->form_data['link'];
      $link_works = validate_url($link);
      $checkbox_broken_unticked  = ($this->form_data['broken'] == 'true') ? false : true;

      if ($link_works == $checkbox_broken_unticked) {
        // No error to report do nothing
      }
      elseif ($link_works == true && $checkbox_broken_unticked == false) {
          $this->waring_messages[] = "Link reported as broken - but it now appears to be working";
      }
      elseif ($link_works == false && $checkbox_broken_unticked == true) {
          $this->waring_messages[] = "The server cannot access <a href='$link' target='_blank'>$link</a> please check the link is correct. If the site is dead <a href='http://web.archive.org/web/*"."/$link' target='_blank'>the wayback machine</a> may provide an alternitive link. Otherwise you should mark the link as broken, to remove it from the site listings. ";
      }
    }
  }

  function print_example() {
    $hide_fields = array('provisional','deleted');
    $function    = 'article_more_SQL_HTML_by_id';
    $id = $this->primary['articles.id'];

    parent::print_example();
    parent::print_example_auto($hide_fields, $function, $id);
  }
}

?>
