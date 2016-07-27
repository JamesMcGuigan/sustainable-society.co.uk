<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<?php
  if(function_exists('dw_doctitle')) { dw_doctitle(); }
  elseif(function_exists('pagetitle'))
       { echo '<title>Sustainable Society Directory: '; pagetitle(); echo '</title>'; }
  else { echo '<title>Sustainable Society Directory</title>'; }

  if(function_exists('dw_head')) dw_head();

  // $root_dir = ../
  if($_SERVER['SERVER_NAME'] == 'localhost'
  || $_SERVER['SERVER_NAME'] == 'www' ) $root_dir = '/sustainable-society.co.uk/';
  else $root_dir = '/';

//   $root_dir = str_replace($_SERVER['DOCUMENT_ROOT'], '/', __FILE__);
//   $root_dir = substr($root_dir, 0, strrpos($root_dir, '/'));
//   $root_dir = substr($root_dir, 0, strrpos($root_dir, '/')+1);


  echo "<link href='${root_dir}templates/sustainable-society.xml.css' rel='stylesheet' type='text/css' media='all' />"
?>



<!--[if IE]>
<style>
#navbar li ul { display: block;  margin-left:0; }
#navbar .jsexpand li ul { display: none;  margin-left:0; }
</style>
<![endif]-->

</head>

<body>

<?php
@include_once('inc/inc.login.php');
$horizontalbar = "<img src='images/bar.jpg' class='hr' alt='------------------------------------------------------------------' />";
?>

<a name="top"></a>

<div id='logo'>
<span><a href="http://www.nosoftwarepatents.com"><img src="images/swpatbanner.en.png" width="480" height="60" border='0' alt="STOP Software Patents!" target='_blank' style='float:right;margin-top:-10px;' /></a></span>
<div class='indent'></div><em>Sustainable Society</em> Directory
</div>

<div id='top-menu' class='white-links' style='clear:right'>
<a href="./index.htm"  >Home</a> |
<a href="./purpose.htm">Purpose</a>  |
<a href="./sitemap.htm">Site Map</a> |
<a href="http://www.earthemergency.org" >Earth Emergency</a>
</div>

<div id='page-below-header'>
<div id='side-bar'>
<div id='navbar' class='navbar'>

<?php include_once('config/sql.menu.php'); menu(); ?>

</div>
</div>

<div id='body'>
<div id='title'> <?php pagetitle(); ?> </div>
<?php echo $horizontalbar; ?>
<div id='pagequote'> <?php if(function_exists('dw_quote')) dw_quote(); ?> </div>
<div id='content'>
  <?php content(); ?>
</div>

<?php if($fixed_footer) echo "class='footer'"; ?>

<div <?php if(!$fixed_footer) echo "class='footer'"; ?> >
<div> <?php echo $horizontalbar; ?> </div>
<div id='backtotop'><a href='#top'>Back To Top</a></div>
<div> <?php echo $horizontalbar; ?> </div>
</div>

</div>

<?php // @include("debug/inc.print-globals.php"); ?>

</div>

</body></html>