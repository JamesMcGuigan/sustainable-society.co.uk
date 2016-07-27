<?php

require_once('inc/class.db.php'); // imports DB class
include_once('config/html.menu.php');

function menu()
{
  global $admin_mode;

  $menus = array('Start Main','Keywords','End Main');
  if($admin_mode) $menus = array_merge(array('Admin'),$menus);

  foreach($menus as $menu) {
    $data = get_menu_table($menu);
    printmenublock($data);
  }
}

function get_menu_table($menu) {
  global $db;
  $sql = "SELECT id, title, link, submenu FROM menus WHERE menu='$menu' "
//       . 'ORDER BY position';
       . 'ORDER BY if(submenu is null,position,submenu), if(submenu is null,0,1),'
       .         ' position, title, id';
  $result = $db->query($sql);

  // add $id into menulist key and turn 'submenu' into empty array
  $menulist = array();
  while ($row = mysql_fetch_assoc($result)) {
    $row['submenus'] = array();
    $menulist[$row['id']] = $row;
  }

  $in_submenu = array();
  foreach($menulist as $key => $row) {
    //  check if its a submenu of another row (check if referenced row is valid)
    if($row['submenu'] !== null && $row['submenu'] !== '0'
    && isset($menulist[$row['submenu']])) {
      $menulist[$row['submenu']]['submenus'][] = &$menulist[$key]; // sub-sub-menus may be added to submenu later
      $in_submenu[] = $key; // mark for deletion
    }
  }

  // remove elements that have been moved to submenu
  $menulist_clean = array();
  foreach($menulist as $key => $row) {
    if(!in_array($key,$in_submenu)) {
      $menulist_clean[] = $menulist[$key];
    }
  }

  return $menulist_clean;
}


function get_menu_from_keywords() {
  $db = new DB;
  $sql = 'SELECT DISTINCT keyword FROM keywords ORDER BY keyword';
  $result = $db->query($sql);

  while ($entry = mysql_fetch_assoc($result)) {
    $menulist[] = array($entry['keyword'], "listing.php?keyword=" . $entry['keyword']);
  }
  return $menulist;
}

?>