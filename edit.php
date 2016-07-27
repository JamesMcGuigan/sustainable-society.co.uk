<?php

@include_once('debug/inc.debug.php');

include_once('inc/inc.file.php');
include_once('inc/inc.login.php');
$type = $_GET['type'];

if($type === 'menu' && $admin_mode == false) {
  print_login_screen();
  return;
}
else{
  $class_file = "config/class.form-edit-$type.php";
  $class    = "form_edit_$type";
  if(file_exists_incpath($class_file)) {
    include_once($class_file);
  }
  if(class_exists($class)) {
    $form = new $class();
    $form->process_submission();
  } else {
    $form = null;
  }
}

function pagetitle()
{
	global $form;
  if($form == null) {echo "Error, type not specified"; return; }
  $form->print_title();
}

function content()
{
	/* STOP */
	global $form;
  if($form == null) { return; }

	$form->print_errors();
	$form->print_example();
	$form->print_form();
}

include_once('templates/sustainable-society.xml.php');

?>
