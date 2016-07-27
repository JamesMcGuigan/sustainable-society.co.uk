<?php

require_once('inc/class.form.php');
include_once('config/sql.listing.php');
require_once('class.form-subform-keywords.php');
include_once('inc/inc.london_underground_station_array.php');

class form_edit_event extends form {
  // declare primary_field, primary_field_value, table, and spec
  function init_variables() {
    /* STOP*/
    $this->primary_field = 'id';
    $this->table = 'events';

    $this->specs =array(
      array('field' => 'id',
            'type'  => 'fixed',
            'title' => 'ID',
            'is_primary'=>true ),

      array('field' => 'title',
            'type'  => 'text',
            'title' => 'Event Title',
            'required'=>true ),

      array('field' => 'link',
            'type'  => 'text',
            'title' => 'Link',
            'validate_as'=>'link' ),

      array('field' => 'series',
            'type'  => 'combo_alt',
            'title' => 'Event Series'),

      array('field' => 'organizer',
            'type'  => 'combo_alt',
            'title' => 'Event Organizer'),

      array('field' => 'date',
            'type'  => 'date',
            'title' => 'Date',
            'required'=>true,
            ),

      array('field' => 'date_end',
            'type'  => 'date',
            'title' => 'Date End',
            'comments' => 'Leave blank if event only lasts one day'),

      array('field' => 'time',
            'type'  => 'text',
            'title' => 'Time'),

      array('field' => 'venue',
            'type'  => 'combo_alt',
            'title' => 'Venue'),

      array('field' => 'postcode',
            'type'  => 'text',
            'title' => 'Postcode',
            'maxlength'=>'8',
            'width' => '5em'),

      array('field' => 'nearest_tube',
            'type'  => 'combo_alt',
            'title' => 'Nearest Tube',
            'defaults' => london_underground_station_array()),

      array('field' => 'nearest_tube2',
            'type'  => 'combo_alt',
            'title' => 'Nearest Tube2',
            'defaults' => london_underground_station_array()),

      array('field' => 'nearest_tube3',
            'type'  => 'combo_alt',
            'title' => 'Nearest Tube3',
            'defaults' => london_underground_station_array()),

      array('field'      => '',
            'title'      => 'Speakers',
            'type'       => 'subform',
            'subform'    => new form_subform($this, array(//new form_subform_book_author($this),
                            'table' => 'events_speakers',
                            'specs' => array(
                            array('field'      => 'speaker',
                                  'type'       => 'text',
                                  'required'   => true,
                                  'width'      => '70%',
                                  'defaults'   => array(''),
                                ),
                            array('field'      => 'type',
                                  'title'      => 'as',
                                  'type'       => 'combo',
//                                  'width'      => '25%',
                                  'defaults'   => array('speaker','chair'),
                                  'unrequired' => true,
                                ),
/*                            array('field'      => 'description',
                                  'title'      => '<br>notes',
                                  'type'       => 'text',
                                  'width'      => '90%',
                                  'unrequired' => true,
                                ),*/
                            array('field'      => 'event_id',
                                  'type'       => 'hidden',
                                  'is_primary' => true,
//                                  'comments'   => '<br>'
                                ),
                            )))
           ),


      array('field' => 'public_contact',
            'type'  => 'text',
            'title' => 'Contact Person'),

      array('field' => 'booking_required',
            'type'  => 'combo',
            'title' => 'Is Booking Required',
            'width' => '20%',
            'defaults' => array('false','true')),

      array('field' => 'booking_email',
            'type'  => 'text',
            'title' => 'Booking Email',
            'width' => '50%',
            'validate_as'=>'email',
           ),

      array('field' => 'booking_phone',
            'type'  => 'text',
            'title' => 'Booking Telephone',
            'width' => '50%',
           ),

      array('field' => 'booking_other',
            'type'  => 'text',
            'title' => 'Booking Info',
            'width' => '50%',
            ),

      array('field' => 'ticket_price',
            'type'  => 'text',
            'title' => 'Ticket Price',
            'width' => '50%',
            ),

      array('field'      => '',
            'type'       => 'subform',
            'title'      => 'Categories',
            'subform'    => new form_subform_keywords($this) ),

      array('field' => 'more_info',
            'type'=>'textarea',
            'title'=>'More Info Page',
            'rows'=>20,
            ),

      array('field' => 'provisional',
            'type'=>'checkbox',
            'title'=>'Provisional Entry',
            'comments'=>'Uncheck to approve user submitted links and add to public listing',
            'admin_only' => true),

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

      array('field' => 'private_notes',
            'type'=>'textarea',
            'title'=>'Webmaster\'s Notes',
            'comments'=>'Note: These notes will not be displayed in the listing',
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
    $function = 'event_SQL_HTML_by_id';
    $id = $this->primary['events.id'];

    parent::print_example();
    parent::print_example_auto($hide_fields, $function, $id);
  }
}

?>
