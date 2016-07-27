<?php

require_once('inc/class.form.php');
require_once('inc/class.db.php');

class form_edit_menu extends form {
  // declare primary_field, primary_field_value, table, and spec
  function init_variables() {
    global $db;

    $all_keywords_sql = '(SELECT DISTINCT keyword FROM keywords) UNION (SELECT DISTINCT keyword FROM keywordtypes) ORDER BY keyword';
    $all_keywords = $db->single_column($all_keywords_sql);
    $titles = array('');
    $links  = array(null,'< menu gap >');
    foreach($all_keywords as $keyword) {
      $titles[] = ucwords($keyword);
      $links[]  = "listing.php?keyword=$keyword";
    }
//    $last_position = $db->single_scalar("SELECT position FROM menu WHERE menu='$_GET[menu]'") + 1;
    $submenu_result = $db->query("SELECT id, title, menu FROM menus WHERE title <> '' ORDER BY title");
    while($row = mysql_fetch_assoc($submenu_result)) {
      if(!isset($submenu[$row['menu']])) { $submenu[$row['menu']][null] = ''; }
      $submenu[$row['menu']][$row[id].'.0'] = $row['title'] . ' (id:'.$row['id'].')';
    }

    if(!isset($_GET['menu'])) {
      $other_menus = array('Start Main','End Main', 'Admin', 'Keywords');
      $all_menus = $db->single_column("SELECT DISTINCT menu FROM menus");
      $all_menus = array_unique(array_merge($other_menus,$all_menus));
    } else { $all_menus = implode(';',$_GET['menu']); }

    $this->specs = array();
    foreach($all_menus as $menu) {
      $this->specs[] =
      array('field'      => '',
            'title'      => ucfirst($menu) .' Menu',
            'title_comments' => implode('<br/>',$all_keywords) . '<br/>&lt; menu gap &gt;<br/><br/>',
            'table'      => 'menu',
            'type'       => 'subform',
            'subform'    => new form_subform_menu($this, array(//new form_subform_book_author($this),
                            'table' => 'menus',
                            'blank_fields'=> 2,
                            'specs' => array(
                            array('field'      => 'id',
                                  'title'      => '<span style="width:4em;float:left;"><b>ID</b>',
                                  'type'       => 'fixed',
                                  'unrequired' => true,
                                  'comments'   => ' </span> ',
                                ),
                            array('field'      => 'submenu',
                                  'title'      => 'submenu of',
                                  'type'       => 'combo',
                                  'width'      => '30%',
                                  'defaults'   => $submenu[$menu],
                                  'defaults_only'=>true,
                                ),
                            array('field'      => 'position',
                                  'title'      => 'position',
                                  'type'       => 'number',
                                  'width'      => '3em',
                                  'unrequired' => true,
//                                  'defaults'   => array($last_position),
                                ),
                            array('field'      => 'menu',
                                  'type'       => 'combo',
                                  'title'      => 'in menu',
                                  'is_primary' => true,
                                  'unrequired' => true,
                                  'insert_primary'=>true,
                                  'primary_source'=>"TEXT:$menu",
                                  'defaults'   => $other_menus,
                                ),
                            array('field'      => 'title',
                                  'title'      => '<br><b style="width:3em;float:left;">Title</b>',
                                  'type'       => 'text',
                                  'width'      => '85%',
                                  'defaults'   => $titles,
                                 ),
                            array('field'      => 'link',
                                  'title'      => '<br><b style="width:3em;float:left;">Link</b>',
                                  'type'       => 'text',
                                  'defaults'   => $links,
                                  'width'      => '85%',
                                  'comments'   => '<br>'
                                ),
                            )))
           );
    }

    parent::init_variables();
  }

  function print_title() {
    $menu = ucwords(implode(', ',explode(';',$_GET['menu'])));
    $s = (count($this->specs) > 1) ? 's' : '';
    echo "Edit $menu Menu$s";
  }
}

class form_subform_menu extends form_subform {
  function get_form_data_from_database() {
    $table_backup = $this->table;
    $this->table .= " LEFT JOIN {$this->table} as parent "
                  . " ON $table_backup.submenu = parent.id "
                  . " LEFT JOIN {$this->table} as grandparent " 
                  . " ON parent.submenu = grandparent.id "; 
    
    $this->primary_where_sql .=
     " ORDER BY if(grandparent.id is not null, grandparent.position, "
            . "    if(parent.id is not null, parent.position, $table_backup.position)), "
            . " if(grandparent.id is null,0,1), " // put grandparent at top 
            . " if(parent.id is not null, parent.position, $table_backup.position), "
            . " if(parent.id is null,0,1), "      // put parent at top
            . " $table_backup.title, " // sort equal positions alphebetically
            . " $table_backup.id ";    // sort by order created 


              //. "   if(parent.id is not null, parent.position, "
              //. "   $table_backup.position)), ";
//              . "if(parent.id is not null, parent.position, "
//              . "   $table_backup.position)), "
//              . " $table_backup.position, "   
//             .  " title, id ";
    parent::get_form_data_from_database();
    $this->table = $table_backup; // $this->table is used elsewhere in the code
  }

  function fill_combo_boxes() {
    parent::fill_combo_boxes();
  }
}

?>
