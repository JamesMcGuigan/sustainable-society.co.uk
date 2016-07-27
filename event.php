<?php

require_once('config/sql.listing.php');
$id = (isset($_GET['id'])) ? $_GET['id'] : $_SERVER['QUERY_STRING'];

function dw_doctitle() {
  global $id;
  echo "<title>Sustainable Society Directory: ";
  event_title_SQL_HTML_by_id($id);
  echo "</title>";
}


function pagetitle()
{
  global $id;
//  event_title_SQL_HTML_by_id($id);
}

function content()
{
  global $id;
  event_more_SQL_HTML_by_id($id);
}

include_once('templates/sustainable-society.xml.php');

?>