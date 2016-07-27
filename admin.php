<?php

@include_once('debug/inc.debug.php');

include_once('inc/class.db.php'); // creates $connection;
include_once('inc/inc.string.php');
require_once('config/html.listing.php');
require_once('config/sql.listing.php');
require_once('inc/inc.html.php');
include_once('inc/inc.login.php');


function pagetitle()
{
  global $admin_mode;
  if($admin_mode) {
    echo "Admin Page";
  } else {
    echo "Login Page";
  }
}

function content()
{
  global $admin_mode;
  if($admin_mode == false) {
    print_login_screen();
    return;
  }

  global $db;
  echo "<div><a href='edit.php?type=menu'>Edit Menus</a></div><br><br>";

  $link_sql = "SELECT id, title, archive, broken, provisional, deleted FROM links ORDER BY provisional, archive, broken, deleted DESC, title";
  $event_sql = "SELECT id, title, provisional, deleted FROM events ORDER BY provisional, deleted DESC, title";
  $quote_sql = "SELECT id, CONCAT(SUBSTRING(REPLACE(quote, ' ', ' '),1,80),'... ') as 'title', provisional, deleted FROM quotes ORDER BY provisional, deleted DESC, title";
  $book_sql  = "SELECT id, title, provisional, deleted FROM books  ORDER BY provisional, deleted DESC, title";
  $homepage_sql = "SELECT id, "
  . "CONCAT(SUBSTRING(CONCAT(`type`,' - ',`title`,' ',`by`,' - ',`text`),1,80),'... ') as title, "
  . " if(hidden='true' "
  . " || (hidebefore IS NOT NULL AND hidebefore < NOW()) "
  . " || (hideafter IS NOT NULL AND hideafter > NOW()), 'true', 'false') as hidden "
  . " FROM homepage ORDER BY provisional, hidden, deleted DESC, position, id ";

  $keyword_result = $db->query("SELECT id, idtype, keyword FROM keywords");
  $keywords = array();
  while($row = mysql_fetch_assoc($keyword_result)) { // note id types are plural
    $all_keywords[$row['idtype']][$row['id']][] = $row['keyword'];
  }

  //debug('$all_keywords',$all_keywords);

  foreach(array('link','book','event','quote', 'homepage') as $type) {
    $options = array();
    $sql = ${$type.'_sql'};
    $result = $db->query($sql);
    while($row = mysql_fetch_assoc($result)) {
      $comments = array();
      $keywords = array();
      foreach(array('hidden', 'archive', 'broken', 'provisional', 'deleted') as $reason) {
        if($row[$reason] === 'true') { $comments[] = $reason; }
      }
      if(is_array($all_keywords[$type.'s'][$row['id']])) {
        foreach($all_keywords[$type.'s'][$row['id']] as $word) {
          $keywords[] = $word;
        }
      }
      $comments = return_if(' [',implode(', ',$comments),'] ');
      $keywords = return_if(' (',implode(', ',$keywords),') ');
      $options[(string)$row['id']] = $row['title'].(($keywords || $comments) ? (' --- '.$keywords.$comments) : '' );
    }
    $Type = ucfirst($type);

    $combo = "<select name='id' style='width:85%'>";
    foreach($options as $id => $text) { $combo .= "<option value='$id'>$text</option>"; }
    $combo .= "</select>\n";

    echo "<div><a href='edit.php?type=$type&mode=add'>Add New $Type</a></div><br>";
    echo "<div><form action='edit.php' method='get'><input type='hidden' name='type' value='$type'>";
    echo "<input type='submit' style='width:7em' value='Edit $Type'> $combo";
    echo "</form></div>";
    echo "<br>";
  }
}

include_once('templates/sustainable-society.xml.php');

?>
