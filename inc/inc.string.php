<?php

include_once('debug/inc.debug.php');

function quote($var) {
		return "'$var'";
}

function quote_if_string($var) {
	if(is_numeric($var) == false)
		return "'$var'";
	else
		return $var;
}

function datestring_to_datetime($datestring) {
	$timestamp = strtotime($datestring);
	$datetime = date('Y-m-d H:i:s',$timestamp);
	return $datetime;
}

function print_if() {
	$variables = func_get_args();
	foreach($variables as $var) {
		if($var === '' || $var === null || $var === false) {
			return false; // exit function
		}
	}

	// if we got this far, then all variables are valid
	foreach($variables as $var) {
		echo $var;
	}
	return true;
}


function return_if() {
  $variables = func_get_args();
  foreach($variables as $var) {
    if($var === '' || $var === null || $var === false) {
      return ''; // exit function
    }
  }
  // if we got this far, then all variables are valid
  foreach($variables as $var) {
    $return .= $var;
  }
  return $return;
}



function implode_or($seperator, $or, $array) {
   if(count($array) === 0 || is_array($array) == false) {
    return '';
  } elseif(count($array) === 1) {
    foreach($array as $var) { return $var; }
  } else {
    $last_var = array_pop($array);
    $text = implode($seperator, $array) . $or . $last_var;
    return $text;
  }
}

function uc_array($array) {
  if(is_array($array) == false) {
    return array();
  }

  foreach($array as $key => $value) {
    $array[$key] = ucwords($value);
  }
  return $array;
}


function first_not_null() {
  $variables = func_get_args();
  foreach($variables as $var) {
    if($var !== '' && $var !== null && $var !== false) {
      return $var;
    }
  }
  return null;
}

function quote_quotes($var) {
  return str_replace("'","\\'",$var);
}

function strip_last_letter($letter, $string) {
  while($string[strlen($string)-1] === $letter) { $string = substr($string,0,(strlen($string)-1)); } // remove last letter if exists
  return $string;
}

function date_range_to_string($start_date, $end_date, $format='d m Y' ) {
  $start_ts = ($start_date == '') ? -1 : strtotime($start_date);
  $end_ts   = ($end_date   == '') ? -1 : strtotime($end_date);

  // check both are valid or return defaults
  if($start_ts === -1 && $end_ts === -1) { return ''; }
  if($start_ts !== -1 && $end_ts === -1) { return date($format,$start_ts); }
  if($start_ts === -1 && $end_ts !== -1) { return date($format,$end_ts);   }

  // check start_date is before end_date
  if($start_ts > $end_ts) { swap($start_ts, $end_ts); swap($start_date, $end_date);}

  $len = strlen($format);
  for($i=0;$i<=$len;$i++) {
    if($format[$len-($i+1)] === '\\') $i++; // check if its an escaped char
    $subformat = substr($format,$len-$i,$i);
    if(date($subformat,$start_ts) !== date($subformat,$end_ts)) {
      break;
    }
  }

  $start_part = date(substr($format,0,$len-($i-1)),$start_ts);
  $end_date   = date($format, $end_ts);

  if($start_part != '') { $date_range = $start_part . ' to '; }
  $date_range .= $end_date;

  return $date_range;
}


function implode_range($seperator, $start, $end, $add_zeros=true) {
  $array_int = range($start, $end);
  if($add_zeros == false) { return implode($seperator,$array_int); }
  else {
    $array = array();
    $digit_count = strlen("$end");
    foreach($array_int as $number) {
      $string = "$number";
      $array[] = $string;
      if(strlen($string) != $digit_count) {
        $array[] = str_pad($string, $digit_count, '0', STR_PAD_LEFT);
      }
    }
    return implode($seperator,$array);
  }
}

function date_uk_to_american($date) {
  $days   = implode_range('|',0,12,true);
  $months = implode_range('|',0,30,true);
  $year = '\d{2}|\d{4}';
  //$whitespace = '[\W\/-\\\\]{1,3}';
  $non_alpha = '[^0-9a-zA-Z]+';
  return preg_replace( "/^($days)($non_alpha)($months)($non_alpha)($year)$/", '$3$2$1$4$5', $date);
}




?>