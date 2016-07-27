<?php

@include_once('debug/inc.debug.php');

include_once('inc/class.db.php'); // creates $connection;
include_once('inc/inc.string.php');
require_once('config/html.listing.php');
require_once('config/sql.listing.php');


$all_types = array('quotes','events','books','links');
$keywords = first_not_null($_GET['keyword'],$_GET['keywords'],(($_GET['type']) ? null : $_SERVER['QUERY_STRING']) );
$type     = ($_GET['type'] == null) ? $all_types : explode(';', $_GET['type']);

function pagetitle()
{
  global $db, $keywords, $type, $all_types;

  $lastslash = strrpos($_SERVER['PHP_SELF'], '/');
  $filename = substr($_SERVER['PHP_SELF'], $lastslash + 1, strlen($_SERVER['PHP_SELF'])) . "?" . $_SERVER['QUERY_STRING'];

  // Get title from menu
  $title_sql = ' SELECT CONCAT_WS(" ", t3.title, t2.title, t1.title), t1.title, t2.title, t3.title FROM menus AS t1 '
             . ' LEFT JOIN menus as t2 ON (t1.submenu = t2.id ) '
             . ' LEFT JOIN menus as t3 ON (t2.submenu = t3.id ) '
             . " WHERE t1.link = '$filename' " ;
  if($title = $db->single_scalar($title_sql)) {
    echo $title;
  } else {

    // Work out a title from the keywords specified
    if($type === $all_types) { $typetext = ' '; }
    elseif(count($type) > 0) { $typetext = implode_or(', ',' and ',uc_array($type)); }

    // books or events onlys
    if(count($type) <= 1) { }
    // no keywords
    elseif(strlen($keywords) == 0) {
      $keywordtext = ' All ';
    }
    // only one keyword
    elseif(strpos($keywords,'?') === false && strpos($keywords,';') === false) {
      $keywordtext = ' '.ucwords($keywords).' ';
    }
    // only ANDs
    elseif(strrpos($keywords,'?') === false) {
      $keywordtext = implode_or(', ',' and ',uc_array(explode(';',$keywords)));
    }
    // only ORs
    elseif(strrpos($keywords,';') === false) {
      $keywordtext = implode_or(', ',' and ',uc_array(explode('?',$keywords)));
    }

    echo "$keywordtext $typetext";
  }
}

function content()
{
  global $keywords, $type, $horizontalbar, $admin_mode;
  $float_right = array('books','events');
  $function_var = array('quotes'=>'quoteHTML','events'=>'eventHTML','books'=>'bookHTML','links'=>'linkHTML');
  $display_titles = array();

  if($admin_mode || (count($keywords) == 0 && $_GET['type'] == null )) {
    $link_limit  = null;
    $book_limit  = 1;
    $event_limit = null;
    $quote_limit = null;
  } else {
    $link_limit  = 5;
    $book_limit  = 5;
    $event_limit = 5;
    $quote_limit = 1;
  }

  if($type === array('books')) { //only type books
    $books  = book_SQL_by_keywords       ($keywords);
    $links  = link_SQL_by_keywords       ('Books');
    $quotes = quote_SQL_by_keywords_rand ('Books', $quote_limit);
    $events = event_SQL_by_keywords_limit('Books',5);
    $float_right = array('links','events');
    $additional_html = books_additional_html();
  }
  elseif($type === array('events')) { // only type events
    $events_week   = event_SQL_by_keywords_date($keywords, 'now',     'this Sunday');
    $events_future = event_SQL_by_keywords_date($keywords, 'this Sunday', '+3 year');
    $events_old    = event_SQL_by_keywords_date($keywords, '-3 year', 'now'        );
    $books  = book_SQL_by_keywords_rand  ('Events', $book_limit);
    $links  = link_SQL_by_keywords_rand  ('Events', $link_limit);
    $quotes = quote_SQL_by_keywords_rand ('Events', $quote_limit);
    $float_right = array('links','books');
    $display_titles =  array('events_week'=>'Events This Week','events_future'=>'Future Events','events_old'=>'Previous Events');
    $function_var   += array('events'=>'eventHTML','events_week'=>'eventHTML','events_future'=>'eventHTML','events_old'=>'eventHTML');
    $additional_html = events_additional_html();
  }
  else { // more than one type
    if(in_array('quotes', $type)) {
      $quotes = quote_SQL_by_keywords_rand ($keywords, $quote_limit);
    }
    if(in_array('events', $type)) {
      $events = event_SQL_by_keywords_limit($keywords, $quote_limit);
    }
    if(in_array('books', $type)) {
      $books  = book_SQL_by_keywords_rand  ($keywords, $book_limit);
    }
    if(in_array('links', $type)) {
      if(in_array('links', $float_right))
        $links = link_SQL_by_keywords_rand($keywords, $links_limit);
      else
        $links = link_SQL_by_keywords($keywords);
    }
  }


  // filter out which elements are blank and the last displayed item
  $display = array();
  foreach($function_var as $var => $function) {
    if(count($$var) > 0) {
      $display[$var] = $function;
      if(!in_array($var, $float_right)) {
        $last_var = $var;
      }
    }
  }

  foreach($display as $var => $function) {
    if(in_array($var, $float_right)) {
      echo "<div class='side-listing' style='clear:right'>";
      echo '<h3>'.ucwords($var).'</h3>';
      print_if("<h3>",$display_titles[$var],"</h3>");
      echo "<ul>";
      foreach($$var as $value) { call_user_func($function,$value,false); }
      echo "</ul>";
      echo "</div>";
    } elseif($var === 'quotes') {
      echo "<ul>";
      foreach($$var as $value) { call_user_func($function,$value,true); }
      echo "</ul>";
      if($last_var !== $var) echo $horizontalbar;
    } else {
      print_if("<h3 class='display_titles'>",$display_titles[$var],"</h3>");
      echo "<ul>";
      foreach($$var as $value) { call_user_func($function,$value,true); }
      echo "</ul>";
      if($last_var !== $var) echo $horizontalbar;
    }
  }

  echo $additional_html;
}

include_once('templates/sustainable-society.xml.php');


function books_additional_html() {
return <<<ENDOFHTML
  <ul class='book' id='content-book'>
    <li>
      <p><span class="firebrick">Beatley, Timothy</span>: <b>'Green
        Urbanism, Learning from European Cities'</b><br>
        Island Press, 2000. <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Bello, Walden/Bullsrd, Nicola/Malhotra,
        Kamal</span>:<b> 'Global Finance, New Thinking on Regulating
        Speculative Capital Markets'</b><br>
        Zed Books, 2000. <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Boyle, David</span>: <b>'Why London
        Needs Its Own Currency'</b><br>
        NEF Pocketbook, 2000. <br>
        ISBN 1-899407-27-8 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Brown, Lester R/Flavin, Christopher/French,
        Hilary and others</span>:<b> 'State of the World 2001'</b><br>
        The Worldwatch Institute, 2001.<br>
        ISBN 0-393-32082-0 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Bruges, James</span>: <b>'The Little
        Earth Book'</b><br>
        Alastair Sawday Publishing Co. Ltd, 2000. <br>
        ISBN 1-901970-23-X <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Bunzl, John</span>: <br>
        <b>'The Simultaneous Policy - An Insider's Guide to Saving Humanity
        and the Planet' </b><br>
        With Foreword by Diana Schumacher.<br>
        New European Publications, 2001. <br>
        ISBN 1-872410-20-0 <br>
        <a href="books/sp.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Capra, Fritjof </span>:<b> 'The Hidden Connections'</b><br>
        HarperCollins, 2001<br>
        ISBN 0006551580<br>
        <a href="books/capra.htm">Read More</a> <br>
      </p>
    </li>              <li>
      <p><span class="firebrick">Douthwaite, Richard</span>:<b> 'Short
        Circuit'</b><br>
        Green Books, 1996.<br>
        ISBN 1-870098-64-1<br>
        <a href="books/circuit.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Douthwaite, Richard</span>: <b>'The
        Ecology of Money'</b><br>
        A Schumacher Briefing published by Green Books on behalf of
        The Schumacher Society, 1999. <br>
        ISBN 1-870098-81-1 <br>
        <a href="books/ecol.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Douthwaite, Richard</span>:<b> 'The
        Growth Illusion. How Economic Growth has Enriched the Few, Impoverished
        the Many and Endangered the Planet.' </b><br>
        The Lilliput Press Ltd, 2000 (revised edition). <br>
        ISBN 1-901866-32-7 <br>
        <a href="books/growth.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Douthwaite, Richard/Jopling, John</span>:
        '<b>FEASTA Review.'</b> <br>
        Green Books Ltd, 2001. <br>
        ISBN 0-9540510-0-9 <br>
        Ekins, Paul/Hillman, Mayer/Hutchinson, Robert: 'The Gaia Atlas
        of Green Economics'<br>
        Anchor Books, 1992. <br>
        ISBN 0-385-41914-7 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Elkington, John/Hailes, Julia</span>:
        '<b>The New Foods Guide'</b><br>
        Orion Books Ltd, 1999. <br>
        ISBN 0-575-06806-X <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Girardet, Herbert</span>:<b> 'Creating
        Sustainable Cities'</b><br>
        A Schumacher Briefing published by Green Books on behalf of
        The Schumacher Society. <br>
        ISBN 1-870098-77-3 more <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Girardet, Herbert</span> :<b> 'The
        Gaia Atlas of Cities. New directions of sustainable urban living'</b><br>
        Gaia Books, 1992. <br>
        ISBN 1-85675-065-5 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Goldsmith Edward/Mander Jerry</span>:<b>
        'The Case Against the Global Economy, &amp; for a turn towards
        localization'</b><br>
        Earthscan, 2001. <br>
        ISBN 1-85383-742-3 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Hassed, Mark</span>: '<b>The Prosperity
        Paradox. The Economic Wisdom of Henry George - Rediscovered'</b><br>
        Chatsworth Village, 2000. <br>
        ISBN 1-876677-73-2 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Hawken, Paul/Lovins, Amory B/Lovins,
        L Hunter</span>:<b> 'Natural Capitalism. The Next Industrial
        Revolution.'</b><br>
        Earthscan 1999. <br>
        <a href="books/capital.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Hines, Colin</span>:<b> 'Localization.
        A Global Manifesto'</b><br>
        Earthscan Publications Ltd, 2000. <br>
        ISBN 1-85383-612-5<br>
        <a href="books/localise.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Huber, Joseph/Robertson, James</span>:
        <b>'Creating New Money. A monetary reform for the information
        age'</b><br>
        New Economics Foundation, 2001. <br>
        ISBN 1-899407-29-4 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Jopling, John</span>: <b>'London. Pathways
        to the Future'</b><br>
        Published by the Sustainable London Trust, 2000. <br>
        ISBN 0-9537680-0-7<br>
        <a href="books/london.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Keen, Steve</span>:<b> 'Debunking Economics.
        The Naked Emperor of the Social Sciences'</b><br>
        Pluto Press, 2001. <br>
        ISBN 1-86403-070-4<br>
        <a href="books/debunk.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Khor, Martin</span>:<b> 'Rethinking
        Globalization. Critical Issues and Policy Choices'</b><br>
        Zed Books, 2001. <br>
        ISBN 1-84277-054-3 Hardback <br>
        ISBN 1-84277-055-1 Paperback <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Klein, Naomi</span>:<b> 'NO LOGO'</b><br>
        Flamingo, 2000. <br>
        ISBN 0-00-255919-6 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Korten, David C</span>:<b> 'When Corporations
        Rule the World'</b><br>
        Earthscan Publications Ltd, 1995. <br>
        ISBN 1-85383-434-3 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Korten, David C</span>: <b>'The Post-Corporate
        World: Life After Capitalism'</b><br>
        Kumarian Press and Berrett-Koehler Publishers, 1999. <br>
        ISBN 1-887208-03-8<br>
        <a href="books/post.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Kumar, Satish</span>:<b> 'You Are,
        Therefore I Am. A Declaration of Dependence'</b><br>
        Green Books 2002. <br>
        ISBN 1 903998 18 2<br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Lang, Tim/Hines, Colin</span>:<b> 'The
        New Protectionism'</b><br>
        Earthscan Publications Ltd, 1993. <br>
        ISBN 1-85383-165-4<br>
        <a href="books/protect.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Lietaer, Bernard</span>:<b> 'The Future
        of Money. Creating New Wealth, Work and a Wiser World.'</b><br>
        Random Century, 2000. <br>
        ISBN 0-7126-8399-2 <br>
        <a href="books/future.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Lovelock, James</span>:<b> 'Gaia. A
        New Look at Life on Earth'</b><br>
        OXFORD University Press. <br>
        Reissued with a new preface and corrections in 2000. <br>
        ISBN 0-19-286218-9 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Mitchell, Stacy</span>:<b> 'The Home
        Town Advantage. How to Defend Your Main Street Against Chain
        Stores ... and Why it Matters.'</b><br>
        Institute of Local Self Reliance. <br>
        ISBN 0-917582-89-6 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Monbiot, George</span>:<b> 'Captive
        State. The Corporate Takeover of Britain'</b><br>
        MacMillan Publishers Ltd, 2000. <br>
        ISBN 0-333-90164-9 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Myers, Norman/Kent, Jennifer</span>:<b>
        'Perverse Subsidies. Tax $s Undercutting Our Economies and Environments
        Alike'</b><br>
        IISD, 1998. <br>
        ISBN 1-895536-09-X<br>
        <a href="books/perverse.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Nixon, Bruce</span>:<b> 'Global Forces.
        A guide for enlightened leaders - what companies and individuals
        can do'</b><br>
        Management Books 2000. <br>
        ISBN 1-85252-353-0<br>
        <a href="books/forces.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Norberg-Hodge, Helena</span>:<b> 'Ancient
        Futures. Learning from Ladakh' </b><br>
        Rider, 1991.<br>
        ISBN 0-7126-5231-0 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Norberg-Hodge, Helena/Goering, Peter/Page,
        John</span>: <b>'From the Ground Up, Rethinking Industrial Agricolture'</b><br>
        Zed Books, second (revised) edition, 2001.<br>
        ISBN 1-85649-993-6 Hb <br>
        ISBN 1-85649-994-4 Pb <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Palast, Greg</span>:<b> 'The Best Democracy
        Money Can Buy' </b><br>
        Pluto Press 2002.<br>
        ISBN 0 7453 1846 <br>
        <a href="http://www.gregpalast.com">www.gregpalast.com</a> </p>
    </li>
    <li>
      <p><span class="firebrick">Robertson, James</span>:<b> 'Beyond
        the Dependancy Culture. People, Power and Responsibility'</b><br>
        Adamantine Press Limited, 1998.<br>
        <a href="books/beyond.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Robertson, James</span>: <b>'Transforming
        Economic Life. A Millennium Challenge'</b><br>
        A Schumacher Briefing published by Green Books on behalf of
        The Schumacher Society. <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Rowbotham, Michael</span>:<b> 'The
        Grip of Death. A Study of Modern Money, Debt Slavery and Destructive
        Economics'</b><br>
        John Carpenter Publishing, 1998.<br>
        <a href="books/grip.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Rowbotham, Michael</span>: <b>'Goodbye
        America! Globalisation, debt and the dollar empire'</b><br>
        Jon Carpenter Publishing, 2000. <br>
        ISBN 1-897766-56-4 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Schlosser, Eric</span>: <b>'Fast Food
        Nation. What the All-American Meal is Doing to the World'</b><br>
        Penguin Books, 2001. <br>
        ISBN 0-713-99602-1 <br>
        <a href="books/fast.htm">Read More</a> <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Simms, Andrew</span>:<b> 'An Environmental
        War Economy. The lessons of ecological debt and global warming.'</b><br>
        NEF Pocketbook, 2001 <br>
        ISBN 1-899-407-391 <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Singh, Kavaljit</span>:<b> 'Taming
        Global Financial Flows. A Citizen's Guide'</b><br>
        Zed Books, 2000. <br>
        ISBN 1-85649-783-6 hardback <br>
        ISBN 1-85649-784-4 paperback<br>
        <a href="books/taming.htm">Read More</a></p>
    </li>
    <li>
      <p><span class="firebrick">Smith, J.W.</span>:<b> 'The World's Wasted Wealth'</b><br>
        The Institute for Economic Democracy, 1994 <br>
        ISBN 0-9624423-3-X <br>
      </p>
    </li>
    <li>
      <p><span class="firebrick">Spowers, Rory</span>:<b> 'Rising Tides.
        A History of Environmental Revolution and Visions for an Ecological
        Age'</b><br>
        Canongate Books 2002<br>
        ISBN 1 84195 246 X<br>
      </p>
    </li>
  </ul>
ENDOFHTML;
}

function events_additional_html() {
global $horizontalbar;
return <<<ENDOFHTML
  $horizontalbar<br />
  <ul class='event' id='content-event'>
    <li><a href="eventsSS.htm">Past "Creating a Sustainable Society" events</a><br><br></li>
    <li><a href="events2003.htm">Past Events in 2003</a><br><br></li>
    <li><a href="events2002.htm">Past Events in 2002</a><br><br></li>
    <li><a href="events2001.htm">Past Events in 2001</a><br></li>
  </ul>
ENDOFHTML;
}

?>
