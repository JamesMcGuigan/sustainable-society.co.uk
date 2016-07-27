<?php

include_once('inc/class.db.php'); // global $db;
include_once('config/html.listing.php');
include_once('inc/inc.admin_mode.php');
include_once('inc/inc.misc.php');
include_once('inc/inc.string.php');
include_once('debug/inc.debug.php');


$link_fields  = "links.id, title, link, description";
$book_fields  = "books.id, title, publisher, year, author, sortby, revision, link, provisional, deleted, title, link, (more_info != '') as is_more_info, contact";
$event_fields = "events.id, date, date_end, time, link, title, series, organizer, venue, postcode,  nearest_tube,   nearest_tube2, nearest_tube3, booking_required, booking_email, booking_phone, booking_other, public_contact, ticket_price, (more_info != '') as is_more_info, contact";
$quote_fields = "quotes.id, quote, source, author";
$homepage_fields = 'homepage.id, position, type, title, by, text, linktext, link, side, border, style, class';
$article_fields = "article.id, title, by, source, sortby, date, link, summary, article_text";


function keyword_where_SQL($keyword_string, $table, $table_id_field='id') {
  // ; = AND
  // ? = OR

  $keyword_array = array();
  $and_or_array  = array();
  $start = 0;
  for($i=0;$i<strlen($keyword_string);$i++) {
    if($keyword_string{$i} == ';' || $keyword_string{$i} == '?') {
      if($string = substr($keyword_string,$start,$i-$start)) { // filter out 0 length strings
        $keyword_array[] = $string;
        if($keyword_string{$i} == ';') { $and_or_array[] = ' AND '; }
        if($keyword_string{$i} == '?') { $and_or_array[] = ' OR '; }
      }
      $start = $i+1;
    }
  } // catch the last bit of the string
  if($string = substr($keyword_string,$start,$i-$start)) { $keyword_array[] = $string; }

  for($i=0;$i<count($keyword_array);$i++) {
    $tables .= ", keywords AS k$i ";
    $join   .= " $table.$table_id_field = k$i.id AND k$i.idtype = '$table' AND ";
    $search .= " k$i.keyword = '$keyword_array[$i]' ";
    if($i+1 != count($keyword_array)) { $search .= $and_or_array[$i]; } // don't add "AND" if last entry
  }

  if(count($keyword_array) == 0) { return " WHERE 1 "; } // in case no keywords are given

  $where = "$tables WHERE $join ( $search ) ";

  return $where;
}

function id_where_SQL($id_array, $id_field='id') {
//  $id_array = array_keys($array);
  if(count($id_array) > 0) {
    foreach($id_array as $id) {
      $id_where[] = " $id_field = '$id' ";
    }
    $id_where = '( '. implode(' OR ', $id_where) .' )';
  } else {
    $id_where = ' 0 ';
  }
  return $id_where;
}

function date_where_SQL($type, $field, $start_date, $end_date=null) {
  $date_formats = array('datetime'=>'Y-m-d H:i:s', 'date'=>'Y-m-d', 'time'=>'H:i:s');

  $start = ($start_date !== null) ? strtotime($start_date) : -1 ;
  $end   = ($end_date   !== null) ? strtotime($end_date)   : -1 ;

  if($start !== -1 && $end !== -1 && $start > $end) { swap($start, $end); }

  if($format !== 'timestamp') {
    $format = $date_formats[$type];
    if($start !== -1) { $start = date($format,strtotime($start_date)); }
    if($end   !== -1) { $end   = date($format,strtotime($end_date));   }
  }

  if($start !== -1) { $date_where .= " AND date >  '$start' "; }
  if($start !== -1) { $date_where .= " AND date <= '$end' "; }

  return $date_where;
}



function link_SQL($fields, $where, $filter=false, $order_by='title', $limit=null ) {
  global $db;
  if(strpos($where, 'WHERE') === false) $where .= ' WHERE 1 ';

  $sql  = ' SELECT DISTINCT ' . $fields
        . ' FROM links '
        . $where;
  if($filter) {
  $sql .= ' AND broken <> "true" '
        . ' AND provisional <> "true" '
        . ' AND deleted <> "true" ';
  }
  $sql .= return_if(' ORDER BY ',$order_by);
  $sql .= return_if(' LIMIT ',$limit);

  $result = $db->query($sql);
  $return = array();
  while ($row = mysql_fetch_assoc($result)) { $array[] = $row; } // load result into array
  return $array;
}

function link_SQL_by_id($id) {
  $array = link_SQL('*',"WHERE id = '$id'",false);
  return array_shift($array);
}

function link_SQL_by_keywords($keywords) {
  global $link_fields;
  $keyword_where = keyword_where_SQL($keywords, 'links','id');
  $array = link_SQL($link_fields,$keyword_where,true);
  return $array;
}

function link_SQL_by_keywords_rand($keywords, $limit=null) {
  global $link_fields;
  $keyword_where = keyword_where_SQL($keywords, 'links','id');
  $array = link_SQL($link_fields,$keyword_where,true,'RAND()',$limit);
  return $array;
}



function book_SQL($fields, $where, $filter=false, $order_by='if(sortby is null, author, sortby), author, title', $limit=null ) {
  global $db;
  if(strpos($where, 'WHERE') === false) $where .= ' WHERE 1 ';

  $sql  = ' SELECT DISTINCT ' . $fields
        . ' FROM books '
        . $where;
  if($filter) {
  $sql .= ' AND provisional <> "true" '
        . ' AND deleted <> "true" ';
  }
  $sql .= return_if(' ORDER BY ',$order_by);
  $sql .= return_if(' LIMIT ',$limit);

  $array = array();
  $result = $db->query($sql);
  while($row = mysql_fetch_assoc($result)) {
//    $row['is_more'] = ($row['more_info'] != '') ? true : false;
    $array[$row['id']] = $row;
  }
  $id_where = id_where_SQL(array_keys($array), 'book_id');

//  $sql_authours   = "SELECT DISTINCT book_id, author, author_type FROM books_authors WHERE $id_where"; // authour, forwarder, with
  $sql_isbn       = "SELECT DISTINCT book_id, isbn, isbn_type FROM books_isbns   WHERE $id_where";

  $result = $db->query($sql_isbn);
  while($row = mysql_fetch_assoc($result)) {
    $array[$row['book_id']]['isbns'][] = array('isbn'=>$row['isbn'],'isbn_type'=>$row['isbn_type']);
  }

//   $authour_types = array();
//   $result = $db->query($sql_authours);
//   while($row = mysql_fetch_assoc($result)) {
//     $array[$row['book_id']][$row['author_type'].'s'][] = $row['author'];
//     $authour_types[$row['author_type'].'s'] = $row['author_type'].'s';
//   }
//
//   // sort and reformat authours
//   foreach(array_keys($array) as $book_id) {
//     foreach($authour_types as $authour_type) {
//       $array[$book_id][$authour_type] = sort_format_last_name($array[$book_id][$authour_type]);
//     }
//   }

  return $array;
}

function book_SQL_by_id($id) {
  $array = book_SQL('*',"WHERE id = '$id'",false);
  return array_shift($array);
}

function book_SQL_by_keywords($keywords) {
  global $book_fields;
  $keyword_where = keyword_where_SQL($keywords, 'books','id');
  $array = book_SQL($book_fields,$keyword_where,true);
  return $array;
}

function book_SQL_all() {
  global $book_fields, $db;
  return book_SQL($book_fields, '', true );
}

function book_SQL_by_keywords_rand($keywords, $limit=null) {
  global $book_fields;
  $keyword_where = keyword_where_SQL($keywords, 'books','id');
  $array = book_SQL($book_fields,$keyword_where,true,'RAND()',$limit);
  return $array;
}



function event_SQL($fields, $where, $filter=false, $order_by='date' , $limit=null) {
  global $db;
  if(strpos($where, 'WHERE') === false) $where .= ' WHERE 1 ';

  $sql  = ' SELECT DISTINCT ' . $fields
        . ' FROM events '
        . $where;
  if($filter) {
  $sql .= ' AND provisional <> "true" '
        . ' AND deleted <> "true" ';
  }
  $sql .= return_if(' ORDER BY ',$order_by);
  $sql .= return_if(' LIMIT ',$limit);

  $array = array();
  $result = $db->query($sql);
  while($row = mysql_fetch_assoc($result)) {
    $row['is_more'] = ($row['more_info'] != '') ? true : false;
    $array[$row['id']] = $row;
  }

  $id_where = id_where_SQL(array_keys($array), 'event_id');

  $sql_speakers = " SELECT event_id, speaker, type, description FROM events_speakers WHERE $id_where ";

  $result = $db->query($sql_speakers);
  while($row = mysql_fetch_assoc($result)) {
    if($row['type'] == '') { $row['type']='speaker'; } // bugfix
    $array[$row['event_id']][$row['type'].'s'][] = array('speaker'=>$row['speaker'],'description'=>$row['description']);
  }

  return $array;
}

function event_SQL_by_id($id) {
  $array = event_SQL('*',"WHERE id = '$id'",false);
  return array_shift($array);
}

function event_SQL_by_keywords($keywords) {
  global $event_fields;
  $keyword_where = keyword_where_SQL($keywords, 'events','id');
  $array = event_SQL($event_fields,$keyword_where,true);
  return $array;
}

function event_SQL_by_date($start_date, $end_date) {
//   global $event_fields
  $date_where = " WHERE 1 ";
  $date_where .= date_where_SQL('date', 'date', $start_date, $end_date);
  $array = event_SQL($event_fields,$keyword_where,true);
  return $array;
}

function event_SQL_by_keywords_date($keywords, $start_date, $end_date, $order_by='date', $limit=null) {
  global $event_fields;
  $where = keyword_where_SQL($keywords, 'events','id');
  $where .= date_where_SQL('date','date',$start_date,$end_date);
  $array = event_SQL($event_fields,$where,true,$order_by,$limit);
  return $array;
}

function event_SQL_by_keywords_next($keywords, $date) {
  return event_SQL_by_keywords_date($keywords, 'now', $date);
}

function event_SQL_by_keywords_limit($keywords, $limit) {
  return event_SQL_by_keywords_date($keywords, 'now', '+1 year',$limit);
}


function quote_SQL($fields, $where, $filter=false,$order_by='source, author' , $limit=null ) {
  global $db;
  if(strpos($where, 'WHERE') === false) $where .= ' WHERE 1 ';

  $sql  = ' SELECT DISTINCT ' . $fields
        . ' FROM quotes '
        . $where;
  if($filter) {
  $sql .= ' AND provisional <> "true" '
        . ' AND deleted <> "true" ';
  }
  $sql .= return_if(' ORDER BY ',$order_by);
  $sql .= return_if(' LIMIT ',$limit);

  $result = $db->query($sql);
  $return = array();
  while ($row = mysql_fetch_assoc($result)) { $array[] = $row; } // load result into array
  return $array;
}

function quote_SQL_by_id($id) {
  $array = quote_SQL('*',"WHERE id = '$id'",false);
  return array_shift($array);
}

function quote_SQL_by_keywords($keywords) {
  global $quote_fields;
  $keyword_where = keyword_where_SQL($keywords, 'quotes','id');
  $array = quote_SQL($quote_fields,$keyword_where,true);
  return $array;
}

function quote_SQL_by_keywords_rand($keywords, $limit) {
  global $quote_fields;
  $keyword_where = keyword_where_SQL($keywords, 'quotes','id');
  $array = quote_SQL($quote_fields,$keyword_where,true,'RAND()',$limit);
  return $array;
}


function homepage_SQL($fields, $where=null, $filter=true, $order_by='position, id', $limit=null) {
  global $db;
  if(strpos($where, 'WHERE') === false) $where .= ' WHERE 1 ';

  $sql  = ' SELECT DISTINCT ' . $fields
        . ' FROM homepage '
        . $where;
  if($filter) {
  $sql .= ' AND provisional <> "true" '
        . ' AND deleted <> "true" '
        . ' AND (hidebefore IS NULL OR hidebefore < NOW() ) ' // now < tommorrow
        . ' AND (hideafter  IS NULL OR hideafter  > NOW() ) ' // now > yesterday
        . ' AND hidden <> "true" ';
  }
  $sql .= return_if(' ORDER BY ',$order_by);
  $sql .= return_if(' LIMIT ',$limit);

  $result = $db->query($sql);
  $return = array();
  while ($row = mysql_fetch_assoc($result)) { $return[] = $row; } // load result into array
  return $return;
}

function homepage_SQL_by_id($id) {
  $array = homepage_SQL('*',"WHERE id = '$id'",false);
  return array_shift($array);
}


function article_SQL($fields, $where, $filter=false, $order_by='date', $limit=null ) {
  global $db;
  if(strpos($where, 'WHERE') === false) $where .= ' WHERE 1 ';

  $sql  = ' SELECT DISTINCT ' . $fields
        . ' FROM articles '
        . $where;
  if($filter) {
  $sql .= ' AND broken <> "true" '
        . ' AND provisional <> "true" '
        . ' AND deleted <> "true" ';
  }
  $sql .= return_if(' ORDER BY ',$order_by);
  $sql .= return_if(' LIMIT ',$limit);

  $result = $db->query($sql);
  $return = array();
  while ($row = mysql_fetch_assoc($result)) { $return[] = $row; } // load result into array
  return $return;
}

function article_SQL_by_id($id) {
  $array = article_SQL('*',"WHERE id = '$id'",false);
  $return = array_shift($array);
  if(!is_array($return)) { $return = array(); }
  return $return;
}

?>