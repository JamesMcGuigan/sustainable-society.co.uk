<?php
// +----------------------------------------------------------------------
// | PHP Source
// +----------------------------------------------------------------------
// | Copyright (C) 2005 by James McGuigan <james@tux>
// +----------------------------------------------------------------------
// |
// | Copyright: See COPYING file that comes with this distribution
// +----------------------------------------------------------------------
//

ini_set(include_path,ini_get(include_path).':..');

include_once('inc/inc.validate.php');
include_once('inc/inc.string.php');
include_once('inc/inc.array.php');
include_once('inc/inc.html.php');

$errors = array();
$tests  = array();
$tests_run    = 0;
$tests_failed = 0;


function assert_equals($actual, $expected, $strict=true) {
  global $errors, $tests_run, $tests_failed;
  $tests_run++;
  if( ($actual !== $expected)
  || (($actual !=  $expected) && $strict == false) ) {

    ob_start();
    ob_implicit_flush(0);
    debug('__Actual', $actual);
    debug('Expected', $expected);
    $messaage = ob_get_contents();
    ob_end_clean();

    $errors[] = "<b>FAILED:<\br> $messaage";
    return false;
  }
  return true;
}

function assert_true($var, $strict=false) {
  return assert_equals($var, true, $strict);
}

function assert_false($var, $strict=false) {
  return assert_equals($var, false, $strict);
}

function assert_null($var, $strict=true) {
  return assert_equals($var, null, $strict);
}

function assert_equals_array($function, $array, $line=null) {
  foreach($array as $input => $expected) {
    assert_equals(call_user_func($function,$input),$expected) or error($function.'()',$line);
  }
}

function error($message=null, $line=null) {
               $return .= "<br>\n";
  if($line)    $return .= "Line $line: ";
  if($message) $return .= "$message failed";
  echo $return;
}

function test($function) {
  global $errors, $tests;
  $error_count = count($errors);
  call_user_func($function);
  if($error_count == count($errors)) {
    $tests[] = "PASSED: $function()";
  } else {
    $tests[] = "<b>FAILED: $function()</b>";
  }
}

function summary() {
  global $errors, $tests_run, $tests_failed, $tests;

  foreach($tests as $test) { echo $test."<br>\n"; }

  if(count($errors) == 0) {
    echo "<h2 style='color:green'> All Tests Passed </h2>";
  } else {
    echo "<h2 style='color:red'> Some Tests Have Failed </h2>";
  }
  echo "<b>Tests Failed: ".count($errors).'<br>';
  echo "Tests Passed: ".($tests_run - count($errors)).'<br>';
  echo "Total Tests Run: ".$tests_run.'<br></b>';

  echo "<br><br>\n\n";

  foreach($errors as $error) { echo $error."<br>\n"; }
}

function test_email_encode() {

  $data = array(
    'james@starsfaq.com' =>
    '&#106;&#097;&#109;&#101;&#115;&#064;&#115;&#116;&#097;&#114;&#115;&#102;&#097;&#113;&#046;&#099;&#111;&#109;',

    'james@worldfuturecouncil.org' =>
    '&#106;&#097;&#109;&#101;&#115;&#064;&#119;&#111;&#114;&#108;&#100;&#102;&#117;&#116;&#117;&#114;&#101;&#099;&#111;&#117;&#110;&#099;&#105;&#108;&#046;&#111;&#114;&#103;',
  );

  foreach($data as $input => $expected) {
    assert_equals(email_encode($input),$expected) or error('email_encode()',__LINE__);
  }
}

function test_sort_format_last_name() {
  $input = array(
    'James McGuigan',
    'Francescsa Romana Giordano',
    'Mose',
    'von Uexkull, Ole',
    'Jakob #von Uexkull',
    'Richard *Stallman',
    '*Me',
    'Mr A*B C'
   );
   $input = sort_format_last_name($input);

   $expected = array(
    'Mr A B C',
    'Francescsa Romana Giordano',
    'James McGuigan',
    'Me',
    'Mose',
    'Richard Stallman',
    'Jakob von Uexkull',
    'Ole von Uexkull',
   );

   assert_equals($input, $expected, true) or error('sort_format_last_name()',__LINE__);
}


function test_suite() {
  test('test_email_encode');
  test('test_sort_format_last_name');
  summary();
}
test_suite();


?>
