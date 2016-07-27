<?php

function array_extract(&$array, $key) {
  if(! is_array($array)) return null;
  $return = $array[$key];
  unset($array[$key]);
  return $return;
}


function array_distinct($vars) {
	if(is_array($vars) == false) $vars = array($vars);
	$return = array();
	foreach($vars as $var) {
		if(is_array($var) == false && $var != '')
			$return[] = $var;
		elseif($var != '')
			$return = array_merge($return, $var);
	}
	$return = array_unique($return);
	return $return;
}

function make_array($var) {
	if(is_array($var)) return $var;
	if(is_null($var))  return array();
	else return array($var);
}

function in_is_array($var, $array) {
  if(is_array($array) === false)
    return false;
  else
    return in_array($var, $array);
}


function sort_format_last_name($name_array) {
  if(!is_array($name_array)) return array();

  $first_name = array();
  $last_name = array();
  foreach($name_array as $name) {
    if(strpos($name,'#') !== false) {
      list($first,$last) = explode('#',$name,2);
    }
    elseif(strpos($name,'*') !== false) {
      list($first,$last) = explode('*',$name,2);
    }
    elseif(strpos($name,',') !== false) {
      // LastName, Firstname
      list($last,$first) = explode(',',$name,2);
    }
    elseif(strpos($name,' ') !== false) {
      $array = explode(' ',$name);
      $last = array_pop($array);
      $first = implode(' ',$array);
    }
    else { // one word name
      $first = '';
      $last = $name;
    }

    // strip trailing and ending whitespace
    $first = preg_replace('/^\s*|\s*$/','',$first);
    $last  = preg_replace('/^\s*|\s*$/','',$last);

    $first_name[] = $first;
    $last_name[]  = $last;
  }
  array_multisort($last_name, $first_name);
  $name_array = array();
  for($i=0;$i<count($last_name);$i++) {
    $name_array[] = return_if($first_name[$i],' ').$last_name[$i];
  }
  return $name_array;
}


include_once('debug/inc.debug.php');


?>