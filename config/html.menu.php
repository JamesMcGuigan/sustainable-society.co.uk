<?php

include_once('inc/inc.string.php');

function printmenublock($menulist,$is_submenu=false)
{
  if(!is_array($menulist)) { return; }

  // divide into menu block sections
  $menublocks = array();
  $count = 0;
  foreach($menulist as $row) {
    if($row['title'] == '' || $row['link'] === '< menu gap >') {
      // move to next array and skip entry
      $count++;
    } else {
      $menublocks[$count][] = $row;
    }
  }

  foreach($menublocks as $key => $null) {
    $block = &$menublocks[$key];

    reset($block);
    $first = &$block[key($block)];
    $first['is_first'] = true;

    end($block);
    $last = &$block[key($block)];
    $last['is_last'] = true;

    foreach($block as $row_key => $row) {
      $block[$row_key]['menu-self'] = menupointtoself_array($row,false);
      $block[$row_key]['menu-root'] = menupointtoself_array($row,true);
    }
  }

  // print  submenus
  foreach($menublocks as $key => $null) {
    echo "<ul style='position:relative'>";
    foreach($menublocks[$key] as $row) {
      print_link($row,$is_submenu);
    }
    echo "</ul>";
  }
}


function print_link($row, $is_submenu=false) {
  global $root_dir;

  // SELECT id, title, link, submenu FROM menus
  if($is_submenu == false) {
    if($row['is_first'])  $firstlast  = 'first';
    if($row['is_last'])   $firstlast .= 'last'; // = firstlast if both
  }
  if($row['menu-self']) { $navbarself = ' class="navbar-self"'; }
  if($row['menu-root']) { $navbarroot = ' class="navbar-root"'; }

  if(is_array($row['submenus']) && count($row['submenus']) > 0) {
    $submenus = true;
  }

  $row['title'] = htmlspecialchars($row['title']);

  static $unique = 1; $unique++;
  $menuid = "menu$unique";

//   if($submenu && !$navbarroot) {
//     $javascript = "onmouseover='menuexpand(\"$menuid\")' onmouseout='menucontract(\"$menuid\")' onload='menucontract(\"$menuid\")'";
//   }

  if($firstlast) { echo "<span class='$firstlast'>"; }
  if($submenus)   { echo "<span class='submenuroot'>"; }
  echo "<li$navbarroot id='$menuid' $javascript>";
  echo "<a href='$root_dir$row[link]' $navbarself>$row[title]</a>";
  if(is_array($row['submenus']) && count($row['submenus']) > 0) {
    printmenublock($row['submenus'],true);
  }
  echo "</li>\n";
  if($submenus) echo "</span>";
  if($firstlast) { echo "</span>"; }
}


function setDIR()
{
  $phpself = $_SERVER['PHP_SELF'];
  $lastslash = strrpos($_SERVER['PHP_SELF'], '/');
  $GLOBALS['DIRECTORY'] = substr($phpself, 0, $lastslash + 1);
  $GLOBALS['FILENAME'] = substr($phpself, $lastslash + 1, strlen($phpself));
}
setDIR();


function menupointtoself_array($array,$count_submenus=false) {
  if(!is_array($array)) return false;
  $link = $GLOBALS['DIRECTORY'].$array['link'];
  $self = $_SERVER['REQUEST_URI'];
  if($link === $self) return true;
  if($count_submenus == true
  && is_array($array['submenus']) && count($array['submenus']) > 0)
  {
    foreach($array['submenus'] as $submenu) {
      if(menupointtoself_array($submenu) == true) return true;
    }
  }

  // didn't find anything
  return false;
}

// menuitem must contain
// array("title" => "Home", "link" => "index.htm")
function menupointtoself($menuitem)
{
  $target = $GLOBALS['DIRECTORY'] . $menuitem[1];
  if ($target == $_SERVER['REQUEST_URI']) {
    return true;
  } else {
    return false;
  }
}

function getBG($highlight)
{
  if ($highlight == true)
    return '#ffaaff';
  else
    return '#ffffaa';
}


function HTMLmenublockstart($highlight=null)
{
  echo "<ul class='menu'>";
  if($highlight == true) $class = ' class="navbar-self"';
  echo "<div$class></div>";
}

function HTMLmenublockend($highlight=null)
{
  if($highlight == true) $class = ' class="navbar-self"';
  echo "<div$class></div>";
  echo "</ul>";
}

function HTMLmenulink($highlight, $menuitem)
{
  if(is_array($menuitem[1])) {
    echo "<div class='submenu'>";
    printmenublock ($menulist);
    echo "</div>";
  } else {
    $class = ($highlight == true) ? ' class="navbar-self"' : '';
    $title = $menuitem[0];
    $link = $menuitem[1];
    echo "<li$class><a href='$link'>$title</a></li>";
  }
}


// old stype table based menu
/*
function HTMLmenublockstart($highlight)
{
  $bg = getBG($highlight);

  echo "<tr><td>  <table width='100%' align='center' cellpadding='2' cellspacing='0' border='0'><tr><td bgcolor='$bg'><img src='images/spacer.gif' width='1' height='3'></td></tr>\n";
}

function HTMLmenublockend($highlight)
{
  $bg = getBG($highlight);
  echo "<tr><td bgcolor='$bg'><img src='images/spacer.gif' width='1' height='3'></td></tr></table><img src='images/spacer.gif' width='1' height='20'></td></tr>\n";
}

function HTMLmenulink($highlight, $menuitem)
{
  $bg = getBG($highlight);
  $title = $menuitem[0];
  $link = $menuitem[1];

  if ($highlight == false) {
    $class = "sidebar";
    $linkcode = "<a href='$link' class='sidebar'>$title</a>";
  } else {
    $class = "sidebar-selected";
    $linkcode = $title;
  } ;

  echo "<tr><td bgcolor='$bg' align='center' class='$class'>$linkcode</td></tr>\n";
}
*/
?>
