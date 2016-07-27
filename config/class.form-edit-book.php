<?php

require_once('inc/class.form.php');
include_once('config/sql.listing.php');
require_once('class.form-subform-keywords.php');

class form_edit_book extends form {
	// declare primary_field, primary_field_value, table, and spec
	function init_variables() {
		/* STOP*/
		$this->primary_field = 'id';
		$this->table = 'books';

		$this->specs =array(
			array('field' => 'id',
						'type'  => 'fixed',
						'title' => 'ID',
						'is_primary'=>true ),

			array('field' => 'title',
						'type'=>'text',
						'title'=>'Book Title',
						'required'=>true ),

      array('field' => 'author',
            'type'=>'text',
            'title'=>'Author'),

      array('field' => 'sortby',
            'type'=>'text',
            'title'=>'Sort By',
            'comment' => 'Just use the surname of the authour here' ),
                        
			array('field' => 'link',
						'type'=>'text',
						'title'=>'Link',
						'validate_as'=>'link' ),

			array('field' => 'publisher',
						'type'=>'text',
						'title'=>'Publisher'),

      array('field' => 'revision',
            'type'=>'text',
            'title'=>'Revision'),

			array('field' => 'year',
						'type'=>'number',
						'title'=>'Year Published',
						'validate_as'=>'year',
						'width'=>'5em',
						'comments'=>'4 digit year',),


            
// 			array('field'      => '',
//  						'title'      => 'Authour',
// 			      'type'       => 'subform',
// 						'subform'    => new form_subform($this, array(//new form_subform_book_author($this),
// 						                'table' => 'books_authors',
// 														'specs' => array(
// 														array('field'      => 'author',
// 																	'type'       => 'combo_alt',
//  																	'width'      => '38%',    // how wide a text field should be
// 																	'required'   => true,
// 																	'defaults'   => array(''),
// 																),
// 														array('field'      => 'author_type',
// 																	'type'       => 'combo',
// 																	'defaults'   => array('author','forwarder','with'),
// 																	'unrequired' => true,
//                                   'width'      => 'auto'
// 																),
// 														array('field'      => 'book_id',
// 																	'type'       => 'hidden',
// 																	'is_primary' => true       // extracted from $_GET and used in extracting and updating database
// 																),
// 														)))
// 					 ),

			array('field'      => '',
 						'title'      => 'ISBN',
			      'type'       => 'subform',
						'subform'    => new form_subform($this, array(//new form_subform_book_author($this),
						                'table' => 'books_isbns',
														'blank_fields' => 1,
														'specs' => array(
														array('field'      => 'isbn',
																	'type'       => 'text',
																	'width'      => '10em',    // how wide a text field should be
																	'required'   => true,
																),
														array('field'      => 'isbn_type',
																	'type'       => 'combo',
																	'defaults'   => array('','hardback','paperback'),
                                  'width'      => 'auto',    // how wide a text field should be
																	'unrequired' => true,
																),
														array('field'      => 'book_id',
																	'type'       => 'hidden',
																	'is_primary' => true       // extracted from $_GET and used in extracting and updating database
																),
														)))
					 ),

			array('field'      => '',
 						'title'      => 'Keywords',
			      'type'       => 'subform',
						'subform'    => new form_subform_keywords($this) ),

			array('field' => 'more_info',
						'type'=>'textarea',
						'title'=>'More Info Page',
						'rows'=>20,
						'comments'=>'Note: Use HTML code, this will display on a seperate page'),

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
    $function = 'book_SQL_HTML_by_id';
    $id = $this->primary['books.id'];

    parent::print_example();
    parent::print_example_auto($hide_fields, $function, $id);
	}
}

?>
