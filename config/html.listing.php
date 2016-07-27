<?php

include_once('html.listing.php');
include_once('inc/inc.array.php');
include_once('inc/inc.html.php');
include_once('inc/inc.misc.php');

$target_blank = 'target="_blank"';

function linkHTML ($array, $li=true) {
  global $admin_mode,$target_blank;

  $id          = $array['id'];
  $title       = $array['title'];
  $link        = $array['link'];
  $description = $array['description'];
  $archive     = ($array['archive'] == 'true') ? true : false;
  $li = ($li) ? 'li' : 'p';

  if($id == '' || $title == '' || $link == '') {return;} // don't print out blank entries

  echo "<li class='link'>";
  echo "<a href='$link' $target_blank>$title</a>";
  if($description != '')  echo " <br /> $description ";
  if($archive == true)    echo " <br />(Site is currently down, link is via archive.org's WayBack Machine)";
  if($admin_mode == true) echo " <a href='edit.php?type=link&id=$id' class='adminlinks'><font color='blue'>Edit Link</font></a><br/>";
  echo "<br/></li>\n";
}

function link_SQL_HTML_by_id($id) { //note hides admin mode for display
  suspend_admin_mode();
  echo "<ul>"; linkHTML(link_SQL_by_id($id)); echo "</ul>";
  restore_admin_mode();
}


function bookHTML($array, $li=false) {
  global $admin_mode, $target_blank;
  $id          = $array['id'];
  $title       = $array['title'];
  $link        = $array['link'];
  $publisher   = $array['publisher'];
  $year        = $array['year'];
  $author      = $array['author'];
//  $authors     = implode(' / ',array_distinct($array['authors']));
//  $forwarders  = implode(' / ',array_distinct($array['forwarders']));
//  $withs       = implode(' / ',array_distinct($array['withs']));
  $isbns       = make_array($array['isbns']);

//  $is_more     = (bool)$array['is_more'];
  $is_more     = $array['is_more_info'] || (bool)$array['more_info'];
  $revision    = $array['revision'];
  $link        = $array['link'];
  $li = ($li) ? 'li' : 'p';

  if($id == '' || $title == '') {return false;} // don't print out blank entries

  echo "<li class='book'>";
  print_if("<span class='book_authors'>",$author,"</span><b>:</b>"); echo " <span class='book_title'>$title</span><br />";
//  print_if('With: <span class="book_authors">',$withs,'</span><br />');
//  print_if('Forward by: <span class="book_authors">',$forwarders,'</span><br />');
  if(print_if('Published by: ',$publisher)) { print_if(', ',$revision); print_if(' (',$year,') '); echo "<br />"; };
  foreach($isbns as $isbn) { if(print_if("ISBN: ",$isbn['isbn'])) {print_if(', ',ucfirst($isbn['isbn_type'])); echo "<br />";} }
  if($is_more) { echo "<a href='book.php?id=$id'>Read More</a><br />"; }
  if($admin_mode == true) echo " <a href='edit.php?type=book&id=$id' class='adminlinks'><font color='blue'>Edit Book</font></a><br/>";
  echo "</li>";
  return true;
}

function book_more_HTML($array) {
  $notes       = $array['notes'];
  $more_info   = $array['more_info'];

  echo "<div class='book'>";
  echo text_format($more_info);
  echo "</div><br/><br/>";

  $array['is_more_info'] = false;
  $array['more_info']    = '';
  bookHTML($array, false);

  if($admin_mode == true) print_if("Contact Email: <a href'mailto:$contact'",$contact,"</a><br />");
  if($admin_mode == true) print_if("Private Notes: <br />",text_format($notes));
//  echo "</div>";
}

function book_SQL_HTML_by_id($id) {
  suspend_admin_mode();
  echo "<ul>"; bookHTML(book_SQL_by_id($id)); echo "</ul>";
  restore_admin_mode();
}

function book_more_SQL_HTML_by_id($id) {
  echo "<ul>"; book_more_HTML(book_SQL_by_id($id)); echo "</ul>";
}

function book_title_SQL_HTML_by_id($id) {
  $book = book_SQL_by_id($id);
//  $authors = implode(' / ',array_distinct($book['authors']));
  echo "$book[author] - $book[title]";
}

function article_title_SQL_HTML_by_id($id) {
  $article = article_SQL_by_id($id);
  echo $article['title']; print_if("- ", $article['by']);
}

function article_SQL_HTML_by_id($id) {
  suspend_admin_mode();
  echo "<ul>"; article_HTML(article_SQL_by_id($id)); echo "</ul>";
  restore_admin_mode();
}

function article_more_SQL_HTML_by_id($id) {
  #suspend_admin_mode();
  echo "<ul>"; article_more_HTML(article_SQL_by_id($id)); echo "</ul>";
  #restore_admin_mode();
}

function article_HTML($array, $li=false) {
  return article_more_HTML($array, $li);
}

function article_more_HTML($array, $li=false) {
  global $admin_mode, $target_blank;
  extract($array);
  $li = ($li) ? 'li' : 'p';

  if($id == '' || $title == '') { return false; } // don't print out blank entries

  echo "<li class='book'>";
  print_if("<div class='book_title'>",$title,"</div>");
  print_if("<div class='book_authors'> by ",$by,"</div>");
  if($link && $source) { echo "<div><a href='$link'>$source</a><div>"; }
  else {
    print_if("<div>",$source,"</div>");
    print_if("<div><a href='$link'>",$link,"</a></div>");
  }

  if($summary && $article_text
  && stripos(preg_replace('/[\s\n]/','', strip_tags($article_text)),
               preg_replace('/[\s\n]/','', strip_tags($summary))) === false)
  {
    echo "<div class='article_summary'>" . text_format($summary)      . "</div>";
    echo "<div class='article_text'>"    . text_format($article_text) . "</div>";
  } else {
    print_if("<div class='article_text'>", text_format($article_text),  "</div>");
  }

  if($admin_mode == true) echo " <a href='edit.php?type=article&id=$id' class='adminlinks'><font color='blue'>Edit Article</font></a><br/>";
  echo "</li>";
  return true;

}



function eventHTML($array, $li=false) {
  //debug('$array',$array);

  global $admin_mode, $target_blank;
  //$event_fields = "events.id, date, date_end, time, link, title, series, organizer, venue, postcode,  nearest_tube,  nearest_tube2,  nearest_tube3, booking_required, booking_email, booking_other, public_contact, ticket_price";

  extract($array);
  $date_range = date_range_to_string($date, $date_end, 'l jS F Y');
  $li = ($li) ? 'li' : 'p';
  $is_more     = $array['is_more_info'] || (bool)$array['more_info'];

  echo "<li class='event'>";
  if(print_if('<b>',$date_range ,'</b><br/>'));
  print_if($organizer,' presents:<br/>');
  print_if($series,'<br/>');

  if(count($speakers) > 0) { foreach($speakers as $key => $value) { $speaker .= (($key)?', ':' ') . $value['speaker']; } }
  if(count($chairs)   > 0) { foreach($chairs   as $key => $value) { $chair   .= (($key)?', ':' ') . $value['speaker']; } }

  $b_speaker = '<b>Speaker' . ((count($speakers) > 1 || strpos($speaker,',') !== false) ? 's: ' : ': ');
  $b_chair   = '<b>Chair'   . ((count($chairs)   > 1 || strpos($chair  ,',') !== false) ? 's: ' : ': ');

  print_if('<span class="darkorange">"',$title,'"</span><br/>');
  print_if($b_speaker,$speaker,'</b><br/>');
  print_if($b_chair,  $chair,  '</b><br/>');

/*
  if(count($speakers) == 1) {
    $speaker = $speakers[0]['speaker'];
    print_if('<span class="darkorange">"',$title,'"</span><br/>');
    echo "<b>Speakers: $speaker</b><br/>";

  }
  else { // count($speakers) > 1
    print_if('<span class="darkorange">"',$title,'"</span><br/>');
    if(count($speakers)>1) { echo '<i>Speakers:</i><br/>'; } else { echo '<br/><i>Speaker:</i> '; }
    foreach($speakers as $speaker) {
      echo "<span class='darkorange'>$speaker[speaker]</span>";
      print_if(' - ',$speaker['description']);
      echo "<br/>";
    }
  }

  if(count($chairs)>0) {
    if(count($chairs)>1) { echo '<i>Chairs:</i><br/>'; } else { echo '<br/><i>Chair:</i> '; }
    foreach($chairs as $chair) {
      echo "<span class='darkorange'>$chair[speaker]</span>";
      print_if(' - ',$chairs['description']);
      echo "<br/>";
    }
  }

  */
  //if((count($speakers) + count($chairs)) > 0) { echo '<br/>'; }

  if(count($speakers) == 1 && $speakers[0]['description'] != '') {
    echo $speakers[0]['speaker'] . ' - ' . $speakers[0]['description'] . '<br/>';
  }

  print_if("<i>Venue:</i> ", $venue,' '); echo $postcode;
  if($postcode != null) {
    $postcode = urlencode(preg_replace('/[^a-zA-Z0-9]/','',$postcode)); // remove non letter chars
//    $streetmap_title = urlencode("<br/><b>$date_range : $organizer - $series - $title "
//                     . "(<a style='color:darkorange' href='$_SERVER[REQUEST_URI]'>click here to return to event</a>)</b><br/>");
    if(count($speakers) == 1) $speaker_title = $speakers[0]['speaker'] . ': '. $title;
    elseif(count($speakers) > 1) {
      $speaker_title = "$title - Speakers: ";
      foreach($speakers as $key => $speaker) { $speaker_title .= (($key)?'; ':' ')." $speaker[speaker]"; }
    }
    $streetmap_details = implode(' - ',array_distinct(array($organizer, $series, $speaker_title)));
    $streetmap_title = urlencode("$date_range : $streetmap_details");
    $back_link = urlencode("Back to Sustainable Society Event Page");
    $back_url  - urlencode($_SERVER['REQUEST_URI']);
    $streetmap_url = "http://www.streetmap.co.uk/streetmap.dll?postcode2map?code=$postcode&title=$streetmap_title&back=$back_link&url=$back_url";
    echo " <a href='$streetmap_url' $target_blank>View Map</a> ";
  }
  if($venue || $postcode) echo "<br/>";

  if($booking_required === 'true' || $booking_required === true) {
    $booking = ' - Booking is Required ';
    if($ticket_price == '') { $ticket_price = 'free'; }
  }

  print_if('<i>Nearest Tube:</i> ', $nearest_tube, '<br/>');
  print_if('<i>Nearest Tube:</i> ', $nearest_tube2, '<br/>');
  print_if('<i>Nearest Tube:</i> ', $nearest_tube3, '<br/>');
  print_if('<i>Time:</i> ', $time, '<br/>');
  print_if('<i>Tickets:</i> ',$ticket_price,$booking,'<br/>');

  $booking_email = email_encode($booking_email);
  print_if('<i>Contact:</i> ',$public_contact,'<br/> ');
  print_if("<i>Email:</i> <a href='mailto:$booking_email'>",$booking_email,"</a><br/> ");
  print_if('<i>Telephone:</i> ',$booking_phone,'<br/> ');
  $booking_other = highlight_links($booking_other);
  if(substr($booking_other,0,7) !== '<a href'
  && strpos($booking_other,':')) { echo preg_replace('/(.*):(.*)/','<i>$1</i>: $2<br/>', $booking_other); }
  else { print_if("<i>Booking Info:</i> ",$booking_other,'<br/> '); }
  if($link && strpos($link,'://',1) === false) { $link = 'http://' . $link; }
  print_if("<i>Website:</i> <a href='$link' $target_blank>",$link,'</a><br/> ');


  if($is_more == true && $hide_is_more != true) { echo "<a href='event.php?id=$id'>Read More</a><br/>"; }

  if($admin_mode == true) echo " <a href='edit.php?type=event&id=$id' class='adminlinks'><font color='blue'>Edit Event</font></a>";

  echo "</li>";
}




function event_more_HTML($array, $li=false) {
  $array['hide_is_more'] = true;
  extract($array);
  echo text_format($more_info);
  eventHTML($array, $li);
  if($admin_mode == true) print_if("Contact Email: <a href'mailto:$contact'",$contact,"</a><br />");
  if($admin_mode == true) print_if("Private Notes: <br />",text_format($notes));

  echo "<br><div syle='text-align:center'><a href='events.php'>Back to Events Page</a></div>";
}

function event_SQL_HTML_by_id($id) {
  suspend_admin_mode();
  echo "<ul>"; eventHTML(event_SQL_by_id($id)); echo "</ul>";
  restore_admin_mode();
}

function event_more_SQL_HTML_by_id($id) {
  echo "<ul>"; event_more_HTML(event_SQL_by_id($id)); echo "</ul>";
}

function event_title_SQL_HTML_by_id($id) {
  $event = event_SQL_by_id($id);
  extract($event);
  $date_range = date_range_to_string($date, $date_end, 'l jS F Y');
  echo "$title - $date_range";
}

function quoteHTML ($array, $li=false) {
  global $admin_mode;
  // $quote_fields = "quotes.id, quote, from, by";
  extract($array);
  $li = ($li) ? 'p' : 'p';

  if($quote == '') {return;} // don't print out blank entries

  echo "<li class='quote'>";
  echo "<i>",text_format($quote),"</i><br/>";
  echo "<div align='right'>";
  print_if("<b>$source</b>");
  if($source && $author) echo " by ";
  print_if("<span class='book_authors'>",$author,"</span>");
  echo "</div>";
  if($admin_mode == true) echo "<a href='edit.php?type=quote&id=$id' class='adminlinks'><font color='blue'>Edit Quote</font></a>";
  echo "</li>";


}

function quote_SQL_HTML_by_id($id) { //note hides admin mode for display
  suspend_admin_mode();
  echo "<ul>"; quoteHTML(quote_SQL_by_id($id)); echo "</ul>";
  restore_admin_mode();
}


function homepageHTML_all ($entries, $li=true) {
  while(count($entries) > 0) {
    if($entries[0]['side'] === 'wide') {
      echo "<div width='$entries[0][width]' style='margin-left:auto;margin-right:auto'>";
      homepageHTML(array_shift($entries));
      echo "</div>";
      continue;
    }

    $other = null;
    $search_for = ($entries[0]['side'] === 'left') ? 'right' : 'left';

    // search for next entry opposite
    foreach($entries as $key => $entry) {
      if($entry['side'] === $search_for) { $other = $entry; unset($entries[$key]); break; }
      if($entry['side'] === 'wide')      { break; }
    }

    if($other == null) { $entries[0]['side'] = 'wide'; continue; }
    else {
      if($entries[0]['side'] === 'left')  { $left  = array_shift($entries); $right = $other; }
      if($entries[0]['side'] === 'right') { $right = array_shift($entries); $left  = $other; }

      echo "<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
      echo "\n<tr><td width='$left[width]' valign='top'>\n";
      if($left) homepageHTML($left);
      echo "\n</td><td width='$right[width]' valign='top'>\n";
      if($right) homepageHTML($right);
      echo "\n</td></tr>\n";
      echo "</table>\n";
    }
  }
}


function homepageHTML ($array, $li=true) {
  global $admin_mode, $target_blank;

  extract($array); // $homepage_fields = 'homepage.id, type, title, by, text, linktext, link, side, border, style, class';

  if($id == '' || $text == '') {return;} // don't print out blank entries
  if(!$linktext) $linktext = "Read More";

  if($side === 'left')   $class .= ' homepage_left ';
  if($side === 'right')  $class .= ' homepage_right ';
  if($side === 'wide')   $class .= ' homepage_wide ';
  if($border === 'true') $class .= ' homepage_border ';

  echo "<div class='homepage $class' style='$style'>";
  print_if('<p class="homepage_type">',$type,'</p>');
  if($title || $by) {
    if(strpos(strtolower($by),'by') === false) { $By .= ' By '; }
    echo "<p class='homepage_title_by'>";
    if($title) echo "<span class='homepage_title'>$title</span>";
    if($by)    echo "<br/><span class='homepage_by'>$By $by</span>";
    echo "</p>";
  }
  echo '<p class="homepage_text">'.text_format($text).'</p>';
  print_if('<p class="homepage_link"><a href="',$link,"\" $target_blank>",$linktext,'</a></p>');
  if($admin_mode == true) {
    echo "<p><a href='edit.php?type=homepage&id=$id' class='adminlinks'>Edit Entry</a>"
       . " - <a href='edit.php?type=homepage&mode=add' class='adminlinks'>Add Entry</a>"
       . " - (position  $position) </p>";
  }
  echo "</div>\n";
  echo "<div class='homepage_clear_$side'></div>\n";
}

function homepage_SQL_HTML_by_id($id) { //note hides admin mode for display
  suspend_admin_mode();
  echo "<div>"; homepageHTML(homepage_SQL_by_id($id)); echo "</div>";
  restore_admin_mode();
}
/**/

?>