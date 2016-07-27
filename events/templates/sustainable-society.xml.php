<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<?php
  if(function_exists('dw_doctitle')) { dw_doctitle(); }
  else { echo '<title>Sustainable Society Directory: '; pagetitle(); echo '</title>'; }

  if(function_exists('dw_head')) dw_head();
?>

<!-- use the CSS in the root /templates directory -->
<link href="../templates/sustainable-society.xml.css" rel="stylesheet" type="text/css" media="all" />
<link href="/templates/sustainable-society.xml.css" rel="stylesheet" type="text/css" media="all" />

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
$horizontalbar = "<img src='images/bar.jpg' class='hr' alt='------------------------------------------------------------------' />"
?>

<a name="top"></a>

<div id='logo'><div class='indent'></div><em>Sustainable Society</em> Directory</div>

<div id='top-menu' class='white-links'>
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
<div id='title'> <?php pagetitle();?> </div>
<?php echo $horizontalbar; ?>
<div id='pagequote'> <?php if(function_exists('dw_quote')) dw_quote(); ?> </div>
<div id='content'>
  <?php content(); ?>
</div>
<div> <?php echo $horizontalbar; ?> </div>
<div id='backtotop'><a href='#top'>Back To Top</a></div>
<div> <?php echo $horizontalbar; ?> </div>
</div>

<?php @include("debug/inc.print-globals.php"); ?>

</div>

</body></html>