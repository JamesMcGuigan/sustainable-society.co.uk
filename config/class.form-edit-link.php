<?php

require_once('inc/class.form.php');
require_once('class.form-subform-keywords.php');
include_once('config/sql.listing.php');


class form_edit_link extends form {
	// declare primary_field, primary_field_value, table, and spec
	function init_variables() {
		/* STOP*/
		$this->primary_field = 'id';

		$this->table = 'links';

		$this->specs =array(
			array('field'      => 'id',
			      'type'       => 'fixed',
						'title'      => 'ID',
						'is_primary' =>  true ),

			array('field'      => 'title',
			      'type'       => 'text',
						'title'      => 'Title',
						'required'   =>  true,
            'unique'     =>  true,
            ),

			array('field'      => 'link',
			      'type'       => 'text',
						'title'      => 'Link',
						'required'   =>  true,
						'validate_as'=> 'link',
            'unique'     =>  true,
             ),

			array('field'      => 'description',
			      'type'       => 'textarea',
			      'title'      => 'Description',
						'comments'   => 'Note: This will be output as HTML, newlines will not be reconized' ),

			array('field'      => '',
			      'type'       => 'subform',
					  'title'      => 'Categories',
						'subform'    => new form_subform_keywords($this) ),

			array('field'      => 'archive',
			      'type'       => 'checkbox',
						'title'      => 'Link via archive.org',
						'comments'   => 'Check link is via archive.org, will add a comment to the listing',
						'admin_only' =>  true ),

			array('field'      => 'broken',
			      'type'       => 'checkbox',
						'title'      => 'Broken Link',
						'comments'   => 'Check link is via archive.org, will add a comment to the listing',
						'admin_only' =>  true),

			array('field'      => 'provisional',
			      'type'       => 'checkbox',
			      'title'      => 'Provisional Entry',
			      'comments'   => 'Uncheck to approve user submitted links and add to public listing',
			      'admin_only' =>  true ),

			array('field'      => 'deleted',
			      'type'       => 'checkbox',
			      'title'      => 'Deleted Entry',
			      'comments'   => 'Check to remove from the public listing',
			      'admin_only' => 'true' ),

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

	function validate_form() {
		parent::validate_form();

/*    // check link is unique before adding it
		if($this->add_mode && $this->form_submitted) {
      $link = current($_POST[$this->table.'.link']);
			$sql = "SELECT id, title FROM links WHERE link = '$link'";
			$entry = $this->db->single_array($sql);
			if($entry != null) { // there is a duplicate entry
				$duptitle = $entry['title'];
				$dupid    = $entry['id'];
				$this->error_messages[] = "Link <a href='$link'>$link</a> is a duplicate of <a href='edit-link.php?id=$dupid' target='_blank'>Link# $dupid: $duptitle</a>";
			}
		}*/
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
    $hide_fields = array('broken','provisional','deleted');
    $function = 'link_SQL_HTML_by_id';
    $id = $this->primary['links.id'];

    parent::print_example();
    parent::print_example_auto($hide_fields, $function, $id);
	}
}

?>
