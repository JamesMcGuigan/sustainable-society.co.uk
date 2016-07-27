<?php

require_once('class.db.php');
require_once('class.object.php');
require_once('inc.array.php');
require_once('inc.string.php');
require_once('inc.validate.php');
@include_once('debug/inc.debug.php');

// this has all the generic functionality of a form.
// subclasses should be define the $this->spec, $this->table, $this->primary_field, $this->primary_field_value
// and any specialist checks on the form data

// spec definition
//       array('field'      => 'id'       // must equal sql table field
//             'type'       =>|'fixed'    // displays in bold, user cannot change
//                            |'text'     // long text box
//                            |'textarea' // textarea box
//                            |'checkbox' // checkbox
//                            |'modified' // user cannot change, submits as now() on all updates
//                            |'created'  // user cannot change, submits as now() when entry created
//                            |'hidden'   // creates a hidden entry
//                            |'combo'
//                            |'combo_cat'
//            'combo_cat_field'=>'field'
//             'required'   => true       // will validate field as != ''
//             'validate_as'=>|'link'     // is a valid link
//                            |'email'    // is a valid email address
//                            |'year'     // number between 1000 and 2900
//            'primary_source'=>['GET:field'|'PARENT:field'|'TABLE_PARENT'|'FIXED:text']
//             'is_primary' => true       // extracted from $_GET and used in extracting and updating database
//             'admin_only' => true       // only displays and inserts entry if ADMIN_MODE

//             'title'      => 'ID',      // the description used to label the box
//             'comments'   => '',        // text to display after form entry
//             'width'      => '100%',    // how wide a text field should be
//             'rows'       => '5',       // textarea only, how many rows
//            'defaults'   => array()    // sets the default for the field or combo
//             'after_buttons'=> true     // display entry only after the form buttons
//           ),



class form extends object {

// form_edit functions:
//   form_edit()
//   init_variables()
//   validate_url_data()
//   validate_form()
//   make_suggestions()
//   update_database()
//   get_form_data()
//   get_form_data_from_post()
//   get_form_data_from_database()
//   print_errors()
//   print_example()
//   print_form()
//   validate_url($url)
//   valid_email($email)

  // user defined data
  var $specs;            // big 2d array
  var $table;            // set to the SQL table being worked on
  var $is_subform;       // true if form is to be embedded in another form
  var $is_multi_form;    // true if there is more than 1 entry to be displayed
  var $blank_fields;     // number of extra blank fields to display
  var $form_print_size;  // number of times the form is printed out

  // mode flags
  var $mode;
  var $confirm;
  var $admin_mode;
  var $add_mode            = false; // true if adding in a new form
  var $form_submitted      = false; // boolean to test if the form was submitted
  var $entry_not_specified = false; // boolean to test if the form was submitted
  var $entry_not_in_db     = false; // boolean to test if the form was submitted
  var $fields_updated      = 0;

  // error detection variables
  var $error_messages;   // internal list for error messages
  var $warning_messages; // internal list for warnings
  var $error_titles;     // used for highlighting errors in the form

  // data extracted from spec
  var $subforms;         // list of objects that will print out subsections of the form
  var $fields;           // list of all fields relivant to the form
  var $table_fields;     // list of all fields with appended tables
  var $primary;          // array of primaries in field => value format
  var $primary_gets;     // list of primary fields specified by $_GET
  var $primary_table_gets;// list of primary fields specified by $_GET with tables appended
  var $auto_increment_field; // which field is the auto_increment ID
  var $no_increment_field=false; // set true if there is no auto_increment_field

  // globals
  var $db;
  var $constructor_init= null;

  // preprocessed text
  var $redirect;
  var $primary_where_sql;// pre-processed SQL statement for primary table

  // internal processing
  var $form_data;        // internal use for storing the data to print out to the form
  var $form_data_size;   // internal calculation for number of entries in form_data arrays
  var $post_size;        // internal calculation for number of entries in $_POST arrays

  // depreciated vars
  var $primary_field;       // set to the primary_field of the table - REMOVE - replaced by $primary array
  var $serial;

  // NOTE: $parent_form arg only used in form_subform subclass
  function form() {
    // initalizations here will occur BEFORE subclasses
    $this->specs              = array();
    $this->error_messages     = array();
    $this->warning_messages   = array();
    $this->error_titles       = array();
    $this->subforms           = array();
    $this->is_subform         = false;
    $this->is_multi_form      = false;
    $this->form_required      = false;
    $this->blank_fields       = 0;
//    $this->serial             = get_unique_object_serial();

    // allows for dynamic form creation without resorting to subforms
    if(func_num_args() > 0) $this->constructor_init = func_get_arg(0);
  }


  function process_submission() {
    $this->init_variables();
    $this->validate_url_data();
    $this->validate_form();
    $this->update_database();
    $this->get_form_data();

    foreach($this->subforms as $i => $value) {
      $this->subforms[$i]->process_submission();
    }

    $this->redirect();
  }


  function init_variables() {
    $this->db = new DB;
    $this->primary            = array();
    $this->primary_gets       = array();
    $this->primary_table_gets = array();
    $this->subforms           = array();
    $this->fields             = array();
    $this->table_fields       = array();

    if    ($_GET['mode'] === 'add')    { $this->mode = 'add'; }
    elseif($_GET['mode'] === 'delete') { $this->mode = 'delete'; }
    else                               { $this->mode = 'edit'; }

    if    ($_GET['confirm'] === 'ask')     { $this->confirm = 'ask'; }
    elseif($_GET['confirm'] === 'success') { $this->confirm = 'success'; }
    else                                   { $this->confirm = false; }

    $this->form_submitted = ($_POST['form_submitted'] === 'true') ? true : false;
    $this->add_mode = ($_GET['mode'] === 'add') ? true : false;
    $this->admin_mode = $GLOBALS['ADMIN_MODE'];
    $this->serial = get_unique_object_serial();

    // import fields from constructor
    if(is_array($this->constructor_init)) {
      foreach($this->constructor_init as $key => $value) {
        $this->$key = &$this->constructor_init[$key];
      }
    }

    if(!isset($this->type))        { $this->type        = strip_last_letter('s',$this->table); }
    if(!isset($this->form_target)) { $this->form_target = 'edit.php?type='.$this->type.'&';    }


    // convert fields to table_fields and fix $_POST
    foreach ($this->specs as $key => $spec) {
      if($spec['field'] != null) {
         $this->specs[$key]['table_field'] = "{$this->table}.$spec[field]"; // convert all fields to table.field
        $this->fields[]       = $this->specs[$key]['field'];
        $this->table_fields[] = $this->specs[$key]['table_field'];

        // _POST converts . to _ so scan for matching fields in spec and convert
        foreach(array('','_combo_alt') as $combo_alt) {
          $field_converted = str_replace('.','_',$this->specs[$key]['table_field'].$combo_alt);
          if(isset($_POST[$field_converted])) {
            $_POST[$this->specs[$key]['table_field'].$combo_alt] = make_array($_POST[$field_converted]);
            unset($_POST[$field_converted]); // remove old array from entry
          }
        }
      }
    }

    // extra extra data from $this->specs
    $primary_count = -1;
    foreach ($this->specs as $key => $spec) {
      if($spec['type']  === 'subform') { $this->subforms[] = &$this->specs[$key]['subform']; }

      // convert checkbox $_POST 'on' to SQL 'true' or 'false'
      if($spec['type'] === 'checkbox' && $this->form_submitted && $spec['field'] != '') {
        // unticked checkboxes won't generate a $_POST array for their keyword
        if($_POST[$spec['table_field']] == null) $_POST[$spec['table_field']][] = ''; // add non-entry to list

        foreach($_POST[$spec['table_field']] as $i => $value) {
          $_POST[$spec['table_field']][$i] = ($_POST[$spec['table_field']][$i] === 'on') ? 'true' : 'false';
        }
      }

      // merge combo_alt into main array
      if($spec['type']  === 'combo_alt' || $spec['type']  === 'combo_cat_alt') {
        if(is_array($_POST[$spec['table_field']])) foreach($_POST[$spec['table_field']] as $i => $array) {
          if($_POST[$spec['table_field']][$i] == '') {
            $_POST[$spec['table_field']][$i] = $_POST[$spec['table_field']."_combo_alt"][$i];
          }
        }
      }


      // add blank entry to combo boxes if not specified
      if($spec['required'] != true && $spec['unrequired'] != true && substr($spec['type'],0,5) === 'combo') {
        $defaults = &$this->specs[$key]['defaults'];
        if(is_array($defaults) === false || in_array('',$defaults)) { $defaults[] = ''; }
      }

      // add extra comments for textboxes and date fields
      if(!isset($spec['comments'])) {
        if($spec['type'] === 'textarea') {
          $this->specs[$key]['comments'] = 'Formatting Notes: ##Header## &nbsp; *Bold* &nbsp; _Underline_ &nbsp; //Italics// "Quote" &nbsp; >>AlignCenter<< &nbsp; >>AlignRight>> &nbsp; &nbsp; <br/>(Paragraphs are seperated by blank line - &#060;br&#062; to force line break)';
        }
        elseif(in_array($spec['type'],array('date','datetime','time'))) {
          $this->specs[$key]['comments'] = 'Note: UK date format (DD-MM-YYYY) accepted but not American (MM-DD-YYYY). Most textual descriptions can also be used, such as "13th July 1984", "tomorrow", "+1 month".';
        }
      }

      if($spec['auto_increment'] == true) {
        $this->auto_increment_field = $spec['field'];
      }

      // build primary table
      if($spec['is_primary'] == true) {
        $primary_count++;
        $source = explode(':',$spec['primary_source'],2);

        // handle blank entries
        if($source[0] == null) { $source[0] = ($this->is_subform) ? 'PARENT' : 'GET'; }

        if($source[0] === 'GET') {
          $query_field = ($source[1] == null) ? $spec['field'] : $query_field[1];
          $this->primary[$spec['table_field']] = $_GET[$query_field ]; // primary uses table_field as key
          $this->primary_gets[]       = $spec['field'];       // note not $source[1] which would be foreign key
          $this->primary_table_gets[] = $spec['table_field']; // note not $source[1] which would be foreign key
          if($_GET[$query_field] == null) { $this->entry_not_specified = true; }
        }
        elseif($source[0] === 'TEXT') {
          $this->primary[$spec['table_field']] = $source[1];
        }
        elseif($source[0] === 'PARENT') {
          if($source[1] == null) {
            $array_keys = array_keys($this->parent->primary);
            $field = $array_keys[$primary_count];
          }
          else { $field = $source[1]; }
          $this->primary[$spec['table_field']] = $this->parent->primary[$field];
        }
        elseif($source[0] === 'PARENT_TABLE') {
          $this->primary[$spec['table_field']] = &$this->parent->table;
        }
//         elseif($source[0] === 'NULL') {
//           $this->primary[$spec['table_field']] = null;
//         }

      }
    }
    foreach($this->primary as $primary_field => $primary_value) { $primary_where[] = " $primary_field = '$primary_value' "; }
    $this->primary_where_sql = (is_array($primary_where)) ? implode(' AND ', $primary_where) : ' 1 ';


    // set the default auto_increment_field
    if($this->no_auto_increment == true) {
        $this->auto_increment_field = null;
    }
    elseif($this->auto_increment_field == null && array_key_exists($this->table.'.id',$this->primary)) {
      $this->auto_increment_field = 'id';
      foreach($spec as $key => $value) {
        if($spec[$key]['field'] = 'id') {
           $spec[$key]['auto_increment'] = true;
           break; // there is only one field named id
        }
      }
    }

    // get largest size of data entered
    $post_count = 0;
    foreach($this->table_fields as $table_field) {
      $post_count = max($post_count, count($_POST[$table_field]));
    }

    // find out which form rows have data in them
    $data_fields = array();
    for($i=0;$i<$post_count;$i++) {
      foreach($this->specs as $key => $spec) {
        if($_POST[$spec['table_field']][$i] != ''
        && in_array($spec['type'], array('fixed','hidden'))    == false // filter out fields that enter themselves into all arrays
        && in_array($spec['table_field'],array_keys($this->primary)) == false // don't import primary keys
        && $spec['unrequired'] == false
        ) {
          $data_fields[$i] = $i; // use keys to get unique array
          continue; // we found one good entry, skip scanning the rest
        }
      }
    }
    // remove dud lines from $_POST
    foreach($this->table_fields as $table_field) {
      $new_post_line = array();
      foreach($data_fields as $i => $true) {
        $new_post_line[] = $_POST[$table_field][$i];
      }
      $_POST[$table_field] = $new_post_line;
    }
    // cache size of form input
    $this->post_size = count($data_fields);
  }



  function validate_url_data() {
    if($this->add_mode == false && $this->entry_not_specified == true && $this->is_subform == false) {
       $this->error_messages[] = "<b>No Entry Specified - Did you want to <a href='$_SERVER[PHP_SELF]?mode=add&$_SERVER[QUERY_STRING]'>add a new entry</a></b>";
    }

    // Check specified primary_field_value is actually in the database
    if($this->is_subform == false
    && $this->add_mode   == false
    && $this->entry_not_specified == false
    && count($this->primary) > 0)
    {
      foreach($this->primary as $field => $value) { $primary_where_sql[] = " $field = '$value' ";
                                                    $primary_field_sql[] = " $field AS '$field'"; }
      $sql = " SELECT ".implode(',',$primary_field_sql)." FROM $this->table "
           . " WHERE 1 AND " . implode(' AND ',$primary_where_sql);
      $result = $this->db->single_array($sql);
      if($result == null) {
        $this->error_messages[] = "<b>Critical Error - Entry could not be found in database</b>";
        $this->entry_not_in_db = true;
      }
    }
  }

  function validate_form() {
    // don't bother validating the form if it hasn't been submitted
    if($this->form_submitted == true) {
      foreach ($this->specs as $spec_key => $spec)
      {
        // convert to array - allows function to deal with combo boxes
        $post_data = array();
        if(is_array($_POST[$spec['table_field']])) {
          for($i=0;$i<count($_POST[$spec['table_field']]);$i++) {
            $post_data[] = &$_POST[$spec['table_field']][$i];
          }
        }
        else { // shouldn't be required any more
          $post_data[0] = &$_POST[$spec['table_field']];
        }

        // check that all required fields exist
        if($spec['required'] == true) {
          if($spec['type'] === 'subform') {
            $this->specs[$spec_key]['subform']->form_required = true;
          } else {
            $is_present = true; // assume success
            foreach($post_data as $data) { // check all entries are present - post_data has been stripped of blank rows
              if($data == '') $is_present = false;
            }
            if($is_present == false) {
              $title = ($this->is_subform) ? $this->parent_spec['title'] : $spec['title'];
              $this->error_messages[] = (($this->is_subform) ? "A field in " : '') . "<b>$title</b> was required, but is missing";
              $this->error_titles[]   = $title;
            }
          }
        }

        // check that all required fields exist
        if($spec['unique'] == true) {
          foreach($post_data as $data) { // check all entries are present - post_data has been stripped of blank rows
            if($data != null) { // blank entries don't need to be unique
              $title = ($this->subform) ? $this->parent_spec['title'] : $spec['title'];

              $check_unique_sql  = " SELECT $spec[table_field] AS '$spec[table_field]' ";
              foreach($this->primary_table_gets as $pfield) { $check_unique_sql .= ", $pfield AS '$pfield' "; }
              $check_unique_sql .= " FROM {$this->table} WHERE $spec[table_field] = '$data' ";
              $check_unique_sql .= ($this->add_mode) ? '' : " AND NOT ( {$this->primary_where_sql} ) ";

              $result = $this->db->query($check_unique_sql);
              if(mysql_num_rows($result) > 0) { // anything found must be a duplicate
                // find the duplicate in the database
                $query_string_array = array();
                $check_unique_result = mysql_fetch_assoc($result);
                foreach($this->primary_gets as $get_field) {
                  if(($get_value = $check_unique_result["{$this->table}.$get_field"]) != null) {
                    $query_string_array[] = "$get_field=$get_value";
                  }
                }
                $query_string =  implode('&',$query_string_array);

                if($this->is_subform) { $error = 'A field in '; } else { $error = ''; }
                $error .= "<b>$title</b> is a duplicate of ";
                if(count($query_string) == 0) { $error .= 'another entry'; }
                else $error .= "<a href='{$this->form_target}$query_string'>another entry</a>";

                // add to error messages
                $this->error_messages[] = $error;
                $this->error_titles[]   = $title;
              }
            }
          }
        }


        //for($i=0;$i<count($post_data);$i++) {
        foreach($post_data as $i => $null) {
          // check if entry is empty or missing - don't validate further
          if($post_data[$i] == '') continue;

          if(in_array($spec['type'],array('datetime','date','time'))) {
            if($spec['type'] != 'time') { $date = date_uk_to_american($post_data[$i]); } else { $date = $post_data[$i]; }
            if(strtotime($date) === -1) {
                $this->error_messages[] = "<span class='missing-field'>$spec[title]</span> is an invalid date / time specification";
                $this->error_titles[] = $spec['title'];
            }
          }

          if($spec['validate_as'] === 'year') {
            if(is_numeric($post_data[$i]) == false
            || floor($post_data[$i]) != $post_data[$i])
            {
                $this->error_messages[] = "<span class='missing-field'>$spec[title]</span> is an invalid year - should be a 4 digit year'";
                $this->error_titles[] = $spec['title'];
            }
            elseif( $post_data[$i] > 2100
                 || $post_data[$i] < 1400)
            {
                $this->error_messages[] = "<span class='missing-field'>$spec[title]</span> is an invalid year - should be a 4 digit year between 1400 and 2100'";
                $this->error_titles[] = $spec['title'];
            }
          }


          if($spec['validate_as'] === 'link') {/*
            //$link = $_POST[$spec['table_field']];*/

            // convert www. to http://www.
            if(substr($post_data[$i],0,4) === 'www.') {
              $post_data[$i] = "http://" . $post_data[$i]; // update form
              $this->warning_messages[] = "$spec[title] was missing 'http://' - this has been auto-corrected";
            }

            // detect missing http://
            if(substr($post_data[$i],0,7) != 'http://'
            && substr($post_data[$i],0,8) != 'https://'
            && substr($post_data[$i],0,6) != 'ftp://')
            {
              if(in_array($spec['title'],$this->error_titles) == false) { // don't check if already listed as an error
                $this->error_messages[] = "<span class='missing-field'>$spec[title]</span> is missing 'http://'";
                $this->error_titles[] = $spec['title'];
              }
            }
          }

          if($spec['validate_as'] === 'email') {
            $valid_email = validate_email($post_data[$i]);
            if($valid_email === null) {
              $this->error_messages[] = "<span class='missing-field'>$spec[title]</span> is from an invalid mail domain";
              $this->error_titles[] = $spec['title'];
            }
            elseif($valid_email == false) {
              $this->error_messages[] = "<span class='missing-field'>$spec[title]</span> is an invalid email address";
              $this->error_titles[] = $spec['title'];
            }
          }

          if($spec['validate_as'] === 'isbn') {
            $valid_isbn = validate_isbn($post_data[$i]);
            if($valid_email === false) {
              $this->error_messages[] = "<span class='missing-field'>$spec[title]</span> is an invalid ISBN number";
              $this->error_titles[] = $spec['title'];
            }
          }

        }
      } // END foreach ($this->specs as $spec)


      // check if form is required that its been submitted
      if($this->form_required == true ) {
        if($this->post_size == 0) { // no data entered
          if($this->is_subform == false || $this->parent_spec == null) {
            $this->error_messages[] = "Form was required by no data entered";
          } else {
            $this->error_messages[] = "A field in <b>{$this->parent_spec['title']}</b> was required, but is missing";
            $this->error_titles[]   = $this->parent_spec['title'];
          }
        }
      }
    }
  }

  // extra suggestion checks based on the current value of the form - add to $this->warning_messages
  function make_suggestions() {
  }

  function update_database() {
    if(count($this->error_messages) == 0  // don't update database if errors
    && $this->form_submitted == true)    // don't update for a blank entry
    {
      // delete previous
      if($this->is_subform) {
        $sql_delete = "DELETE FROM {$this->table} WHERE " . $this->primary_where_sql;
        $this->db->query($sql_delete);
      }

      for($i=0;$i<$this->post_size;$i++) {
        $set = array();
        $skip_update = false;
        foreach($this->specs as $spec) {
          $field = $spec['table_field'];
          $type  = $spec['type'];
          $date_formats = array('datetime'=>'Y-m-d H:i:s', 'date'=>'Y-m-d', 'time'=>'H:i:s');
          $date_formats_null = array('datetime'=>'0000-00-00 00:00:00', 'date'=>'00-00-00', 'time'=>'00:00:00');

          if(in_array($type,array_keys($date_formats))) { // datetime, date, time
            if($_POST[$spec['table_field']][$i] == '') { $value = 'NULL'; }
            else {
              // convert UK dates to american
              if($type !== 'time') { $_POST[$spec['table_field']][$i] = date_uk_to_american($_POST[$spec['table_field']][$i]); }

              $timestamp = strtotime($_POST[$spec['table_field']][$i]);
              $value = "'".(($timestamp === -1 ) ? 'NULL' : date($date_formats[$type],$timestamp))."'";
            }
          }
          elseif($type === 'number' && ($_POST[$spec['table_field']][$i] == null || !is_numeric($_POST[$spec['table_field']][$i]))) {
            $value = 'NULL';
          }
          // enforces that primary values are entracted from the primary array and not the form
//           elseif($this->is_subform && array_key_exists($spec['table_field'],$this->primary)) {
//             $value = quote($this->primary[$spec['table_field']]);
//           }
          else {
            $value = quote($_POST[$spec['table_field']][$i]);
          }

          if( $type === 'modified'
          || ($type === 'created'        && $this->add_mode == true) ) {
            $set[] = " $field =  NOW() ";
          }
          elseif( $field == ''
              || ($this->add_mode   == false && $type  === 'created')
              || ($this->is_subform == false && in_array($field,$this->primary_table_gets)) )
          {
            // do nothing
          }
          elseif(array_key_exists($field,$this->primary)) {
            // multiple subforms on the same table will result in post_data merging them all together
            // filter out $_POST lines where the $_POST primary field doesn't match the $spec primary field
            // don't filter out lines where the $value is blank, or a quoted blank
            if($this->is_subform && $spec['auto_increment'] == false
            && !($value == '' || $value == "''" || $value === "'".$this->primary[$field]."'"))
            {
              $skip_update = true; // don't submit this line to the db
              break; // goto next $_POST line
            }
            else {
              // take the value from the specs
              $set[] = " $field = '{$this->primary[$field]}' ";
            }
          }
          elseif( $type === 'modified'
              || ($type === 'created'        && $this->add_mode == true) ) {
            $set[] = " $field =  NOW() ";
          }
          else {
            $set[] = " $field = $value ";
          }
        }

        if($skip_update === false) {
          $sql_update = ( ($this->add_mode || $this->is_subform) ? ' REPLACE INTO ' : ' UPDATE ') . $this->table
                      . ' SET  ' . implode(', ', $set)
                      . ( ($this->add_mode || $this->is_subform) ? '' : " WHERE {$this->primary_where_sql}" );

          $this->db->query($sql_update);
          $this->fields_updated += mysql_affected_rows($this->db->connection);
        }
      }

      // Redirect to new page if its a new link thats been submitted
      // dont redirect for subforms or multi-entry forms
      if($this->add_mode       == true
      && $this->form_submitted == true
      && $this->is_subform     == false
      && $this->post_size      == 1
      && count($this->primary_gets) > 0)
      {
        $primary_gets_copy = array();

        // locate last updated id
        $insert_id = mysql_insert_id();
        if($this->auto_increment_field != null) {
          $this->primary[$this->table.'.'.$this->auto_increment_field] = $insert_id;
          $_GET[                          $this->auto_increment_field] = $insert_id;
          $primary_gets_copy[             $this->auto_increment_field] = $insert_id;
        }
        // check if there are more primary_gets than the auto_increment_field
        if($this->primary_gets != array($this->auto_increment_field)) {
          foreach($_GET as $ $key => $value)
            if(array_key_exists($key,$this->primary_gets))
              $primary_gets_copy[$key] = $value;

          // check if there are more primary_gets to be aquired
          if($this->primary_gets != array_keys($primary_gets_copy)) {
            $sql_redirect = ' SELECT '.implode(',',$this->primary_gets)
                          . ' FROM '  . $this->table
                          . ' WHERE ' . str_replace('= NULL','IS NULL',implode(' AND ', $set));
            foreach($primary_gets_copy as $key => $value)
              $sql_redirect .= " AND $key = '$value' ";
            $result = $this->db->single_array($sql_redirect); // find the remainder of the primary gets
            if($result) $primary_gets_copy = $result; // in case nothing was found (an error)
          }
        }

        // update fields needed for subforms and redirect
        foreach($primary_gets_copy as $key => $value) {
          $_GET[$key] = $value;
          $query_string[] = "$key=$value";
        }
        $type = ($this->type) ? $this->type : strip_last_letter('s',$this->table); // make links into link
        $this->redirect = "http://$_SERVER[SERVER_NAME]$_SERVER[PHP_SELF]?type=$type&" . implode('&',$query_string);
        $this->init_variables(); // re-init for the sake of subforms
      }
    }
  }

  function redirect() {
    if($this->add_mode
    && $this->form_submitted
    && count($this->error_messages) == 0
    && $this->is_subform == false
    && $this->redirect != null)
    {
      if(headers_sent() == false) {
        header("Location: {$this->redirect}");
        exit;
      }
      else {
        echo "<b><br>Page should redirect to <a href='{$this->redirect}'>{$this->redirect}</a></b>";
        exit;
      }
    }
  }

  function get_form_data() {
    // check if form hasn't been submitted at it's add mode
    if($this->add_mode == true
    && $this->form_submitted == false) {
      // do nothing leave a blank form
    }
    // check if forn has been submitted with errors
    elseif($this->form_submitted == true
        && count($this->error_messages) > 0 )
    {
      $this->get_form_data_from_post();
    }
    // check if we have an error free form submission OR a new edit request
    elseif( $this->form_submitted == false
        || ($this->form_submitted == true && count($this->error_messages) == 0))
    {
      $this->get_form_data_from_database();
    }

    $this->fill_combo_boxes();

    // safety check - catch blank entries
    foreach($this->table_fields as $table_field) {
      if($this->form_data[$table_field] == null) {
        $this->form_data[$table_field] = array('');
      }
      $this->form_data_size = max(count($this->form_data[$table_field]),$this->form_data_size);
    }

    $this->form_print_size = max($this->form_data_size + $this->blank_fields, $this->blank_fields * 2);
    // fill in primary fields from $this->primary array - after counting form_data_size
    foreach($this->primary as $table_field => $value) {
      $this->form_data[$table_field] = array_fill(0,$this->form_print_size,$value);
    }
  }

  function fill_combo_boxes() {
    // always get combo box fill data from db
    foreach($this->specs as $spec) {
      if($spec['defaults_only'] != true) {
        if($spec['type'] === 'combo_cat' || $spec['type'] === 'combo_cat_alt') {
          if($spec['combo_cat_table'] == null) {
            $sql = " SELECT DISTINCT $spec[table_field] AS '$spec[table_field]', "
                . " $spec[combo_cat_field] AS '$spec[combo_cat_field]' FROM {$this->table} ORDER BY $spec[table_field] ";
          }
          else {
            $sql ="(SELECT DISTINCT $spec[table_field] AS '$spec[table_field]', "
                . " $spec[combo_cat_table].$spec[combo_cat_field] AS '$spec[combo_cat_field]' "
                . " FROM {$this->table} NATURAL LEFT JOIN $spec[combo_cat_table] "
                . ") UNION ( "
                . " SELECT DISTINCT $spec[combo_cat_table].$spec[field], "
                . " $spec[combo_cat_table].$spec[combo_cat_field] "
                . " FROM $spec[combo_cat_table] "
                . ")"
                . " ORDER BY $spec[combo_cat_field], '$spec[table_field]'";
          }
          $result = $this->db->query($sql);
          $defaults = (is_array($this->defaults)) ? $this->defaults : array($this->defaults);
          $combo_options_by_key = array();
          while($row = mysql_fetch_assoc($result)) {
            // create 2D array('keywordtype' => array('Business','Climate'),'keywordtype2' => array('key1','key2'))
            $combo_options_by_key[$row[$spec['combo_cat_field']]][] = $row[$spec['table_field']];
          }

          // add in defaults
          if(is_array($spec['defaults'])) {
            foreach($spec['defaults'] as $key => $value) {
              if(is_int($key)) { $key = ''; }
              $value = make_array($value);
              foreach($value as $default_key => $default) {
                if(!is_array($combo_options_by_key[$key])  // if array doesn't exist its obviously not it in - safety check for in_array
                || !in_array($value,$combo_options_by_key[$key],1) )
                {
                  if(is_int($default_key)) {
                    $combo_options_by_key[$key][]             = $default;
                  } else {
                    $combo_options_by_key[$key][$default_key] = $default;
                  }
                  $resort_fields[] = $key;
                }
              }
            }
          }

          if(is_array($resort_fields)) { foreach($resort_fields as $field) { asort($combo_options_by_key[$field]); } }
            if(isset($combo_options_by_key[null])) {
                // $combo_options_by_key[null] will have been sorted to begining of array, this will send it to the back
                $temp_array = $combo_options_by_key[null];
                unset($combo_options_by_key[null]);
                $combo_options_by_key[null] = $temp_array;
            }

          // place into $combo_options
          $form_data_combo = &$this->form_data[$spec['table_field'].'_combo_options'];
          foreach($combo_options_by_key as $key => $value_array) {
            if($key == '') $key = 'Other';
            $form_data_combo[] = ""; // blank entry
            $form_data_combo[] = "--- $key ---"; // note array key will be an int and not a string
            foreach($value_array as $val_key => $value) {
              if(is_int($val_key)) { $val_key = $value; }
              $form_data_combo[$val_key] = $value;
            }
          }
        }
        elseif($spec['type'] === 'combo' || $spec['type'] === 'combo_alt') { // type == combo
          $sql = "SELECT DISTINCT $spec[table_field] as '$spec[table_field]' "
              . "FROM {$this->table} ORDER BY $spec[table_field] ";
        $combo_options = array_unique(array_merge($spec['defaults'],$this->db->single_column($sql)));
          asort($combo_options);
          foreach($combo_options as $option_key => $option) {
            if(is_int($option_key) || $option_key === null) { $option_key = $option; }
            $this->form_data[$spec['table_field'].'_combo_options'][$option_key] = $option;
          }
        }
      } else { // end if($spec['defaults_only'] != true)
        foreach($spec['defaults'] as $option_key => $option) {
          if(is_int($option_key) || $option_key === null) { $option_key = $option; }
          $this->form_data[$spec['table_field'].'_combo_options'][$option_key] = $option;
        }
      }
    }
  }


  function get_form_data_from_post() {
    // get data from $_POST array
    foreach($this->table_fields as $table_field)
    {
      $_POST[$table_field] = make_array($_POST[$table_field]); // safety check
      for($i=0;$i<$this->post_size;$i++) {
        if($table_field != '') {
          $this->form_data[$table_field][$i] = stripslashes($_POST[$table_field][$i]); // magic_quotes is on
        }
      }
    }
  }

  function get_form_data_from_database() {
    if(count($this->table_fields) == 0) { return; } // needed for subform only forms

    foreach($this->table_fields as $table_field) {
      $date_formats = array('datetime'=>'%D %M %Y - %l:%i %p', // 21st March 2004 6:38 PM
                            'date'    =>'%D %M %Y',            // 21st March 2004
                            'time'    =>'%l:%i %p');           // 6:38 PM
      $type = $this->specs[$table_field]['type'];
      if(in_array($type,array_keys($date_formats))) {
        $table_fields_sql[] = "DATE_FORMAT($table_field,$date_formats[$type]) AS '$table_field'";
      } else {
        $table_fields_sql[] = "$table_field AS '$table_field'"; // keeps table names in SQL results
      }
    }
    $sql_select = ' SELECT ' . implode(', ', $table_fields_sql)
                . ' FROM ' . $this->table
                . ' WHERE '. $this->primary_where_sql;

    //get data from database
    $result = $this->db->query($sql_select);
    while($entry = mysql_fetch_assoc($result)) {
      foreach($entry as $key => $value) {
//        $this->form_data["{$this->table}.$key"][] = $value; // SQL doesn't return table names
        $this->form_data[$key][] = $value; // SQL doesn't return table names
      }
    }

//     // modify
//     foreach()
//
//     debug('$this->form_data',$this->form_data);
//     debug('$specs',$this->specs);

//     elseif(in_array($spec['type'],array('date','datetime','time'))) {
//       $this->specs[$key]['comments'] = 'Note: UK date format (DD-MM-YYYY) accepted but not American (MM-DD-YY). Most textual descriptions can also be used, such as "13th July 1984", "tomorrow", "+1 month".';
//     }

  }


  function print_errors() {
    // print error messages
    if(count($this->error_messages) == 0) {
      if($this->form_submitted == true) {
        // print success message - if form submitted
        $updated = ($this->add_mode) ? " Added " : " Updated ";
        echo "<div class='form_success_message'>Entry was $updated successfully</div>";
        echo $GLOBALS['horizontalbar'];
      }
    }
    else { // we have a problem
      if($this->form_submitted == true) {
        $updating = ($this->add_mode) ? ' adding ' : ' updating ';
        $or_undo     = ($this->add_mode) ? '' : "or <a href='$_SERVER[REQUEST_URI]'>click here</a> to undo your changes";
        echo "<div class='form_failure_message'>There was an error $updating the entry $or_undo</div>";
      }
      echo "<ul class='error_message'>";
      foreach($this->error_messages as $error) {
        echo "<li>".$error."</li>";
      }
      echo "</ul>";
      echo $GLOBALS['horizontalbar'];
    }

    // check if there are any warings to display
    if(count($this->warning_messages) > 0) {
      if($this->form_submitted) {
        echo "<b>The following warnings have not affected your database submission</b>";
      }
      echo "<ul class='warning_message'>";
      foreach($this->warning_messages as $warning) {
        echo "<li>".$warning."</li>";
      }
      echo "</ul>";
      echo $GLOBALS['horizontalbar'];
    }
  }

  function print_title() {
    //$edit = ($form->add_mode) ? 'Add' : (($this->mode === 'delete') ? 'Delete' : 'Edit');
    $Edit = ucfirst($this->mode);
    $Table = ucfirst($this->table);
//    if($Table[strlen($Table)-1] === 's') { $Table = substr($Table,0,(strlen($Table)-1)); } // remove last 's' if exists
    $Table = strip_last_letter('s',$Table);
    echo "$Edit $Table";
  }

  // displays a sample entry for the completed form
  function print_example() {
// // SAMPLE CODE
//     $hide_fields = array('broken','provisional','deleted');
//     $function = 'book_SQL_HTML_by_id';
//     $id = $this->primary['books.id'];
//
//     parent::print_example();
//     parent::print_example_auto($hide_fields, $function, $id) {
  }

  // subclass should call this function from within print_example()
  function print_example_auto($hide_fields, $function, $id) {
    if($this->add_mode == false
    && count($this->error_messages) == 0)
    {
      $reasons = array();
      $hide_fields = make_array($hide_fields);
      foreach($hide_fields as $field) {
        $table_field = $this->table . '.' . $field;
        if($this->form_data[$table_field][0] === 'true') { $reasons[] = $field; }
      }

      if(count($reasons) === 0) {
        echo '<b>This how the entry will be displayed in the listings: </b>';
      } else {
        echo '<b>The following entry will not be displayed in the listings because it is '. implode(' and ',$reasons) . '</b>';
      }
      call_user_func($function, $id);
      echo $GLOBALS['horizontalbar'];
    }
  }





  function print_input(&$spec, $i=0) {
    $title = $spec['title'];
    $field = ($this->is_subform) ? $spec['table_field'].'[]' : $spec['table_field'];
    $value = $this->form_data[$spec['table_field']][$i];
    $value_quoted = str_replace("'",'&#039;',$value);
    $type  = $spec['type'];
    $default=(is_array($spec['defaults'])) ? $spec['defaults'][$i] : $spec['defaults'];
    $class = (in_array($title, $this->error_titles)) ? 'missing-field' : 'field';
    $rows  = (is_numeric($spec['rows'])) ? $spec['rows'] : 5;
        if($spec['width'] != null)        $width = "width:$spec[width]";
    elseif($type == 'number')             $width = '5em';
    elseif($this->is_subform
        && substr($type,0,5) === 'combo') $width = 'width:auto';
    elseif($type == 'combo')              $width = 'width:100%'; // combo, combo_alt, combo_cat, combo_cat_alt
    elseif($type == 'combo_cat')          $width = 'width:100%'; // combo, combo_alt, combo_cat, combo_cat_alt
    elseif($type == 'combo_alt')          $width = 'width:45%';  // combo, combo_alt, combo_cat, combo_cat_alt
    elseif($type == 'combo_cat_alt')      $width = 'width:45%';  // combo, combo_alt, combo_cat, combo_cat_alt
      else                                $width = 'width:100%';

    $tags  = $spec['tags'];
    $maxlength = $spec['maxlength'];
    if(isset($spec['maxlength']) && $type !== 'textarea') { $maxlength = '255'; }


    if (in_array($type, array('fixed', 'hidden'))) {
      if($type === 'fixed') { echo "<nobr><b>$value</b></nobr>"; }
      echo "<input type='hidden' name='$field' value='$value_quoted' $tags>";
    }
    elseif (in_array($type, array('modified', 'created'))) {
      echo "<b>$value</b>";
    }
    elseif(in_array($type,array('text','number'),true)) {
      // datetime conversion preformated by MYSQL
      echo "<input type='text' name='$field' value='$value_quoted' style='$width' maxlength='$maxlength' $tags></input>";
    }
    elseif(in_array($type,array('datetime','date','time'),true)) {
      $date_formats = array('datetime'=>'l j F Y H:i',   // Monday 21 March 2004 18:38
                            'date'    =>'l j F Y',       // Monday 21 March 2004
                            'time'    =>'H:i');          // 18:38
      if($value != null) {
        $timestamp = strtotime($value);
        if($timestamp !== -1) { $value = date($date_formats[$type], $timestamp); }
      }
      // datetime conversion for input preformated by MYSQL
      echo "<input type='text' name='$field' value='$value' style='$width' maxlength='$maxlength' $tags></input>";
    }
    elseif($type === 'textarea') {
      echo "<textarea name='$field' style='$width' rows='$rows' $tags>$value</textarea>";
    }
    elseif($type === 'checkbox') {
      $checked = ($value === 'true') ? ' checked="checked" ' : '';
      echo "<input type='checkbox' name='$field' $checked $tags>";
    }
    elseif($type === 'hidden') {
      echo "<input type='hidden' name='$field' value='$value_quoted' $tags>";
    }
    elseif(substr($type,0,5) === 'combo') { // combo, combo_alt, combo_cat, combo_cat_alt
      $combo_options = $this->form_data[$spec['table_field'].'_combo_options'];
      // write out a combo box
      echo "<select name='$field' style='$width' $tags>";
      foreach($combo_options as $option_value => $option) {
        // numberic indexs mean keyword catageory
        if(is_string($option_value)==false) { $option_value = ''; }
        $option_value = str_replace("'",'&#039;',$option_value);
        $selected = ($option_value == $value) ? 'selected="selected"' : '';
        echo "<option value='$option_value' $selected>$option</option>";
      }
      if(substr($type,count($type)-5,4) === '_alt') { // combo_alt, combo_cat_alt
        echo "<option value=''>Other ---></option>";
      }
      echo "</select>";
    }
    elseif($type === 'subform') {
      $spec['subform']->print_form();
    }

    if($type === 'combo_alt' || $type === 'combo_cat_alt') {
      if(in_is_array($value,$this->form_data[$spec['table_field'].'_combo_options']) == false) { $alt_value = $value_quoted; }
      echo " or <input type='text' name='$spec[table_field]_combo_alt[]' "
                    . " value='$alt_value' style='$width' $tags></input>";
    }
    if($type === 'combo_cat_alt') {
      echo " in ";
      $combo_options = array_keys($this->form_data[$spec['table_field'].'_combo_options']);
      // write out a combo box
      echo "<select name='$spec[combo_cat_field]' style='$width'  $tags>";
      foreach($combo_options as $option) {
        $selected = ($option == $value) ? 'selected="selected"' : '';
        $option = str_replace("'",'&#039;',$option);
        echo "<option value='$option' $selected>$option</option>";
      }
      echo "</select>";
    }

    if($spec['comments'] != '' ) echo "<i> $spec[comments] </i>";
  }

  function print_button($type) {
    $updatetext = ($this->add_mode == true) ? 'Add Entry' : 'Update Entry';
    $get_type = (isset($_GET['type'])) ? 'type='.$_GET['type'].'&' : '';
    if($type === 'submit')
      echo "<input type='submit' value='$updatetext'>";
    elseif($type === 'reset')
      echo "<input type='reset'  value='Reset Form'>";
    elseif($type === 'cancel')
      echo "<a href='$_SERVER[REQUEST_URI]'><button type='button'>Cancel Submission</button></a>";
    elseif($type === 'add')
      echo "<td><a href='$_SERVER[PHP_SELF]?{$get_type}mode=add'><button type='button'>Add New Entry</button></a></td>";
    elseif($type === 'delete')
      echo "<td><a href='$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]&mode=delete'><button type='button'>Delete Entry</button></a></td>";
  }

  function print_add_more_button() {
    // create add_more button
    if($this->is_subform || ($spec['type'] === 'subform' && $this->specs[$spec_key]['subform']->table != null)) {
        $table = ($this->is_subform) ? $this->table : $this->specs[$spec_key]['subform']->table;
        $javascript =
          'var table      = document.getElementById("'.$table.$this->serial.'"); '
        . 'var table_body = table.getElementsByTagName("tbody")[0]; '
        . 'var row = table_body.getElementsByTagName("tr")[0].cloneNode(true); '
        . 'for(var i=0;i<row.getElementsByTagName("option").length;i++) { row.getElementsByTagName("option")[i].selected=""; } '
        . 'for(var i=0;i<row.getElementsByTagName("input" ).length;i++) { '
        . '  if(row.getElementsByTagName("input" )[i].type == "text")     {row.getElementsByTagName("input")[i].value="";} '
        . '  if(row.getElementsByTagName("input" )[i].type == "checkbox") {row.getElementsByTagName("input")[i].checked="";} } '
        . 'table.appendChild(row); ';


        $more_button = "<a onclick='$javascript' style='margin:0'>Add Extra Field</a><br><br>";
        echo $more_button;
    }
  }

  function print_form() {
    if($this->is_subform == false) {
      echo "<form action='$_SERVER[REQUEST_URI]' method='post'>"
         . "<input type='hidden' name='form_submitted' value='true'>"
         . "<table width='100%' id='{$this->table}{$this->serial}' cellspacing='0' cellspacing='0'>";

      foreach(array(true,false) as $after_buttons) {
        foreach($this->specs as $spec_key => $null) {
          $spec = &$this->specs[$spec_key];
          $class = (in_array($spec['title'], $this->error_titles)) ? 'missing-field' : 'field';
          if($spec['after_buttons'] == $after_buttons) { continue; }
          echo "<tr>";
          echo "<td valign='top' class='$class'>$spec[title]:$star";
          echo     "<div class='title-comments'>$spec[title_comments]</div></td>";
          echo "<td class='form' valign='top'>"; $this->print_input($spec); echo "</td>";
          echo "</tr>\n";
        }

        if($after_buttons == false) {
          echo "<tr>";
          echo "<td>&nbsp;</td>";
          echo "<td><table width='100%'><tr>";
          foreach(array('submit','reset','cancel','add') as $button) {
            echo "<td>"; $this->print_button($button); echo "</td>";
          }
          echo "</tr></table></td>";
          echo "</tr>\n";
        }
      }
      echo "</table></form>";
    }
    else { // $this->is_subform == true
      $table = ($this->is_subform) ? $this->table : $this->specs[$spec_key]['subform']->table;
      echo "<table id='{$this->table}{$this->serial}' width='100%'><tr><td width='100%'>";
      for($i=0;$i<$this->form_print_size;$i++) {
        foreach($this->specs as $spec_key => $spec) {
          if($spec['title'] != null) { echo ' '.$spec['title'].': '; }
          $this->print_input($this->specs[$spec_key],$i);
        }
        echo '<br></td></tr><tr><td>';
      }
      echo "</td></tr></table>";
      $this->print_add_more_button();

    } // end else $this->is_subform == true
  }


  function print_form_old() {

    // create add_more button
    if($this->is_subform || ($spec['type'] === 'subform' && $this->specs[$spec_key]['subform']->table != null)) {
        $table = ($this->is_subform) ? $this->table : $this->specs[$spec_key]['subform']->table;
        $javascript =
          'var table      = document.getElementById("'.$table.'"); '
        . 'var table_body = table.getElementsByTagName("tbody")[0]; '
        . 'var row = table_body.getElementsByTagName("tr")[0].cloneNode(true); '
        . 'for(var i=0;i<row.getElementsByTagName("option").length;i++) { row.getElementsByTagName("option")[i].selected=""; } '
        . 'for(var i=0;i<row.getElementsByTagName("input" ).length;i++) { row.getElementsByTagName("input" )[i].value=""; '
        .                                                               ' row.getElementsByTagName("input" )[i].checked=""; } '
        . 'table.appendChild(row); ';

        $more_button = "<button onclick='$javascript'>Add Field</button><br><br>";
        //<button type='button' onclick='$javascript'>More</button>";
    }


    $is_base_form = (!$this->is_subform);
    if($is_base_form) {
      $width = 'width="100%"';
      echo '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">'
         . '<input type="hidden" name="form_submitted" value="true">';
    }
//    echo "<table $width cellpadding='0' cellspacing='0' border='0' class='form'>";
    echo "<table $width id='{$this->table}' cellspacing='0' cellspacing='0'>";

    /* STOP */
    // convert to arrays - should be safe now without
    if($this->form_data == null) $this->form_data = array();
    foreach($this->form_data as $key => $value) {
      $this->form_data[$key] = make_array($value);
    }

    // assume all arrays are the same size
    for($i=0;$i<$this->form_print_size;$i++)
    {
      if($this->is_subform) echo "\n<tr>";


      $true_null = array(true, null);
      foreach($true_null as $first_time) // neeed to skip fields after the buttons
      {
        foreach($this->specs as $spec_key => $spec)
        {
          // hide admin only fields to the public
          if($this->admin_mode == false && $spec['admin_only'] == true) { continue; }

          // skip fields on wrong side of buttons
          if($spec['after_buttons'] == $first_time) { continue; }

          $title = $spec['title'];
          $title_comments = $spec['title_comments'];
          $field = ($this->is_subform) ? $spec['table_field'].'[]' : $spec['table_field'];
          $value = $this->form_data[$spec['table_field']][$i];
          $value_quoted = str_replace("'",'&#039;',$value);
          $type  = $spec['type'];
          $default=(is_array($spec['defaults'])) ? $spec['defaults'][$i] : $spec['defaults'];
          $class = (in_array($title, $this->error_titles)) ? 'missing-field' : 'field';
          $rows  = (is_numeric($spec['rows'])) ? $spec['rows'] : 5;
          $width = ($spec['width'] == null) ? 'width:100%' : "width:$spec[width]";
          $width = ($type === 'number') ? 'width:5em' : $width;
          $width = ($type === 'combo_alt' || $type === 'combo_cat_alt') ? '' : $width;
          $tags  = $spec['tags'];
          $maxlength = $spec['maxlength'];
          if(isset($spec['maxlength']) && $type !== 'textarea') { $maxlength = '255'; }

          $star  = ($spec['required'] == true) ? '<span style="color:red">*</span>' : '';

          if($is_base_form) { echo "<tr><td valign='top' class='$class'>$title:$star $more_button";  }
          echo "<td class='form' valign='top'>";

          if (in_array($type, array('fixed', 'hidden'))) {
            if($type === 'fixed') { echo "<nobr><b>$value</b></nobr>"; }
            echo "<input type='hidden' name='$field' value='$value_quoted' $tags>";
          }
          elseif (in_array($type, array('modified', 'created'))) {
            echo "<b>$value</b>";
          }
          elseif(in_array($type,array('text','number','datetime','date','time'),true)) {
            // datetime conversion preformated by MYSQL
            echo "<input type='text' name='$field' value='$value_quoted' style='$width' maxlength='$maxlength' $tags></input>";
          }
          elseif($type === 'textarea') {
            echo "<textarea name='$field' style='$width' rows='$rows' $tags>$value</textarea>";
          }
          elseif($type === 'checkbox') {
            $checked = ($value === 'true') ? ' checked="checked" ' : '';
            echo "<input type='checkbox' name='$field' $checked $tags>";
          }
          elseif($type === 'hidden') {
            echo "<input type='hidden' name='$field' value='$value_quoted' $tags>";
          }
          elseif(substr($type,0,5) === 'combo') { // combo, combo_alt, combo_cat, combo_cat_alt
            $combo_options = $this->form_data[$spec['table_field'].'_combo_options'];
            // write out a combo box
            echo "<select name='$field' $tags>";
            foreach($combo_options as $option_value => $option) {
              // numberic indexs mean keyword catageory
              if(is_string($option_value)==false) { $option_value = ''; }
              $option_value = str_replace("'",'&#039;',$option_value);
              $selected = ($option == $value) ? 'selected="selected"' : '';
              echo "<option value='$option_value' $selected>$option</option>";
            }
            if(substr($type,count($type)-5,4) === '_alt') { // combo_alt, combo_cat_alt
              echo "<option value=''>Other ---></option>";
            }
            echo "</select>";
          }
          elseif($type === 'subform') {
              $this->specs[$spec_key]['subform']->print_form();
          }


          if($type === 'combo_alt' || $type === 'combo_cat_alt') {
            if(in_is_array($value,$this->form_data[$spec['table_field'].'_combo_options']) == false) { $alt_value = $value_quoted; }
            echo " or <input type='text' name='$spec[table_field]_combo_alt[]' "
                         . " value='$alt_value' style='$width' $tags></input>";
          }
          if($type === 'combo_cat_alt') {
            echo " in ";
            $combo_options = array_keys($this->form_data[$spec['table_field'].'_combo_options']);
            // write out a combo box
            echo "<select name='$spec[combo_cat_field]' $tags>";
            foreach($combo_options as $option) {
              $selected = ($option == $value) ? 'selected="selected"' : '';
              $option = str_replace("'",'&#039;',$option);
              echo "<option value='$option' $selected>$option</option>";
            }
            echo "</select>";
          }

          if($spec['comments'] != '' ) echo "<i> $spec[comments] </i>";
          if($is_base_form) {echo "</td></tr>";} else { echo "</td>"; }
        } // END foreach($this->field as $field)

        if($this->is_subform) {
          echo "</td><td>";
        }

        // print buttons - only first time round - and not in a subform
        if($first_time == true && $is_base_form) {
          $updatetext = ($this->add_mode == true) ? 'Add Entry' : 'Update Entry';
          echo "<tr><td>&nbsp;</td></tr>"; // blank line before buttons
          echo '<tr><td>&nbsp;</td><td><table style="width:100%"><tr>';
          echo "<td><input type='submit' value='$updatetext'></td>";
          echo "<td><input type='reset'  value='Reset Form'></td>";
          echo "<td><a href='$_SERVER[REQUEST_URI]'><button type='button'>Cancel Submission</button></a></td>";
          echo "<td><a href='$_SERVER[PHP_SELF]?mode=add'><button type='button'>Add New Entry</button></a></td>";
          echo "</tr></table></td></tr>";
          echo "<tr><td>&nbsp;</td></tr>"; // blank line under buttons
        }
      } // END foreach(array('true','false') as $afterbuttons)
      if($this->is_subform) { echo "</tr>"; }
    }
    if($is_base_form) { echo "</table></form>"; }
    else {
    echo "</table>$more_button";

    }
  }
}

class form_subform extends form {
  var $parent;
  var $parent_spec;

  // remove
//   var $parent_table;
//   var $parent_primary_field;
//   var $parent_primary_field_value;

  function form_subform(&$parent_form) {

    $other_vars = func_get_args();
    parent::form(&$other_vars[1]); // $parent_form is first var

    $this->parent = &$parent_form;

    // set defaults
    $this->is_subform    = true;
    $this->is_multi_form = true;
    $this->blank_fields  = 1;
  }


  function init_variables() {
    parent::init_variables();

    // TODO remove variables
//     $this->parent_primary_field       = &$this->parent->primary_field;
//     $this->parent_primary_field_value = &$this->parent->primary_field_value;
//     $this->parent_table               = &$this->parent->table;


    // merge data set with parent
//    $this->primary_field_value        = &$this->parent->primary_field_value; // overrides constructor
    $this->add_mode                   = &$this->parent->add_mode;
    $this->error_messages             = &$this->parent->error_messages;
    $this->warning_messages           = &$this->parent->warning_messages;
    $this->error_titles               = &$this->parent->error_titles;
    $this->form_submitted             = &$this->parent->form_submitted; // ($_POST['form_submitted'] == 'true') ? true : false;
    $this->fields_updated             = &$this->fields_updated;

    // find parent spec
    foreach($this->parent->specs as $key => $value) {
      if($this->parent->specs[$key]['subform']->serial === $this->serial) {
        $this->parent_spec = &$this->parent->specs[$key];
        break;
      }
    }
  }
}

?>
