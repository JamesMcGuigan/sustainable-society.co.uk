<?php

include_once('inc.validate.php');
$target_blank = 'target="_blank"';

function html_combo($options, $field=null, $selected=null, $tags=null) {

  echo "<select name='$field' $tags>";
  foreach($options as $option_value => $option) {
    // numberic indexs mean value not specified, use same as display
    if(is_string($option_value)==false) { $option_value = $option; }

    $option_value = str_replace("'",'&#039;',$option_value);
    $select_tag = ($option == $selected) ? 'selected="selected"' : '';
    echo "<option value='$option_value' $select_tag>$option</option>";
  }
  echo "</select>\n";
}

function text_format($text,$is_text_wrapped=true) {

	// HTML comment to include raw html
	if(stristr($text, '<!-- raw html -->') !== false) return $text;

	include_once('inc/inc.validate.php');

  // remove starting and trailing whitespace and blank lines
  $text = preg_replace('/^[\s\n]*/s' ,'',$text);
  $text = preg_replace( '/[\s\n]*$/s','',$text);

  // remove badly displayed charachters
  #$text = preg_replace('/&#([0-9a-f]+);/ei', '1', $text);
  #$text = utf8_to_iso($text);
  #$text = str_replace( array("’","’"),
   #                    array("'","'"),$text);


  // place into paragraph array and add spaces to both sides
  if($is_text_wrapped == true) {
    // strip out lines with only whitespace chars and reduce multiple blank lines to one
    $text = preg_replace('/^(\s)*$/m','',$text);
    $paragraphs = explode("\n\n",$text);
    foreach($paragraphs as $key => $value) { $paragraphs[$key] = ' '.$value.' '; }
  } else {
    $paragraphs = array(' '.str_replace("\n"," <br/>\n ",$text).' '); // extra spaces are needed for pattern matching
  }

  $start  = '%(\s[\*_/><]*)';
  $end    =    "([\*_/><]*\s)%U";
  $middle = "((?![^\S]).*(?![^\S]))";
  $m1 = '((?![^\S])(.(?![';
  $m2 =                  ']{2}))*[\S]*(?![^\S]))';
  $patterns = array(
    $start.'##'.$middle.'##'.$end => '$1<h2 style="text-align:center">$2</h2>$3', // ##Header##
    $start.'\*'.$middle.'\*'.$end => '$1<b>$2</b>$3',                             // *Bold*
    $start.'_' .$middle.'_' .$end => '$1<u>$2</u>$3',                             // _Underline_
    $start.'//'.$middle.'//'.$end => '$1<i>$2</i>$3',                             // //Italics//
    $start.'"' .$middle.'"' .$end => '$1<i>"$2"</i>$3',                           // "Italics"

    $start.'>>'.$m1.'<'.$m2.'>>'.$end => '$1<div style="text-align:right;">$2</div>$3',  // >>AlignRight>>
    $start.'<<'.$m1.'>'.$m2.'<<'.$end => '$1<div style="text-align:left">$2</div>$3',    // <<AlignLeft<<
    $start.'>>'.$m1.'>'.$m2.'<<'.$end => '$1<div style="text-align:center">$2</div>$3',  // >>Center<<
    $start.'<<'.$m1.'<'.$m2.'>>'.$end => '$1<div style="text-align:justify">$2</div>$3', // <<Justify>>
  );
  // run formatting on paragraphs;
  $paragraphs = preg_replace(array_keys($patterns),$patterns,$paragraphs);

  // locate and a href emails and links
  foreach($paragraphs as $key => $value) {
    $paragraphs[$key] = highlight_emails($paragraphs[$key]);
    $paragraphs[$key] = highlight_links ($paragraphs[$key]);
  }

  foreach($paragraphs as $key => $value) {
    $html .= " <p><div>".$paragraphs[$key]."</div></p>\n";
  }
  return $html;
}

// scans the text and
function highlight_emails($text) {
  if(strpos($text,'@') === false) { return $text; } // quick test for emails

  $email_reg = "%([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})(?!\S)%";
  preg_match_all($email_reg,' '.$text.' ',$find);
  foreach($find[0] as $email) {
    list($username,$domaintld) = split("@",$email);
    if(getmxrr($domaintld,$mxrecords)) { // check its from a valid email domain
      $encoded = email_encode($email);
      $ahref = "<a href='mailto:$encoded'>$encoded</a>";
      $text = str_replace($email,$ahref,$text);
    }
  }
  return $text;
}

function highlight_links($text) {
  $text = " $text ";
  global $target_blank;

  $link_reg = '%\s((http://|https://|ftp://|www.)\S*)(?=\s)%';
  preg_match_all($link_reg,"  $text  ",$find);
  for($i=0;$i<count($find[1]);$i++) {
    $link = $find[1][$i]; // find[1] is find[0] without starting space
    $href = (strpos($link,'://') === false) ? 'http://'.$link : $link;
    if(validate_url($href) == true) { // check its a valid url
      $ahref = " <a href='$href' $target_blank >$link</a> ";
      $text = str_replace($find[0][$i],$ahref,$text); // need the starting space to avoid <a href='<a href=''></a>'></a>
      //$text = str_replace($find[0][$i],$ahref,$text);
    }
  }
  return trim($text);
}


// james@starsfaq.com =
// &#106;&#097;&#109;&#101;&#115;&#064;&#115;&#116;&#097;&#114;&#115;&#102;&#097;&#113;&#046;&#099;&#111;&#109;
function email_encode($email) {
  for($i=0;$i<strlen($email);$i++) {
    $decimal = hexdec(bin2hex($email{$i}));
    $encoded .= '&#'.str_pad($decimal, 3, '0', STR_PAD_LEFT).';';
  }
  return $encoded;
}


$cp1252_map = array(
   "\xc2\x80" => "\xe2\x82\xac", /* EURO SIGN */
   "\xc2\x82" => "\xe2\x80\x9a", /* SINGLE LOW-9 QUOTATION MARK */
   "\xc2\x83" => "\xc6\x92",    /* LATIN SMALL LETTER F WITH HOOK */
   "\xc2\x84" => "\xe2\x80\x9e", /* DOUBLE LOW-9 QUOTATION MARK */
   "\xc2\x85" => "\xe2\x80\xa6", /* HORIZONTAL ELLIPSIS */
   "\xc2\x86" => "\xe2\x80\xa0", /* DAGGER */
   "\xc2\x87" => "\xe2\x80\xa1", /* DOUBLE DAGGER */
   "\xc2\x88" => "\xcb\x86",    /* MODIFIER LETTER CIRCUMFLEX ACCENT */
   "\xc2\x89" => "\xe2\x80\xb0", /* PER MILLE SIGN */
   "\xc2\x8a" => "\xc5\xa0",    /* LATIN CAPITAL LETTER S WITH CARON */
   "\xc2\x8b" => "\xe2\x80\xb9", /* SINGLE LEFT-POINTING ANGLE QUOTATION */
   "\xc2\x8c" => "\xc5\x92",    /* LATIN CAPITAL LIGATURE OE */
   "\xc2\x8e" => "\xc5\xbd",    /* LATIN CAPITAL LETTER Z WITH CARON */
   "\xc2\x91" => "\xe2\x80\x98", /* LEFT SINGLE QUOTATION MARK */
   "\xc2\x92" => "\xe2\x80\x99", /* RIGHT SINGLE QUOTATION MARK */
   "\xc2\x93" => "\xe2\x80\x9c", /* LEFT DOUBLE QUOTATION MARK */
   "\xc2\x94" => "\xe2\x80\x9d", /* RIGHT DOUBLE QUOTATION MARK */
   "\xc2\x95" => "\xe2\x80\xa2", /* BULLET */
   "\xc2\x96" => "\xe2\x80\x93", /* EN DASH */
   "\xc2\x97" => "\xe2\x80\x94", /* EM DASH */

   "\xc2\x98" => "\xcb\x9c",    /* SMALL TILDE */
   "\xc2\x99" => "\xe2\x84\xa2", /* TRADE MARK SIGN */
   "\xc2\x9a" => "\xc5\xa1",    /* LATIN SMALL LETTER S WITH CARON */
   "\xc2\x9b" => "\xe2\x80\xba", /* SINGLE RIGHT-POINTING ANGLE QUOTATION*/
   "\xc2\x9c" => "\xc5\x93",    /* LATIN SMALL LIGATURE OE */
   "\xc2\x9e" => "\xc5\xbe",    /* LATIN SMALL LETTER Z WITH CARON */
   "\xc2\x9f" => "\xc5\xb8"      /* LATIN CAPITAL LETTER Y WITH DIAERESIS*/
);

function utf8_to_iso($str) {
   global $cp1252_map;
   return  strtr(utf8_encode($str), $cp1252_map);
}

function iso_to_utf8($str) {
  global $cp1252_map;
  return  utf8_decode( strtr($str, array_flip($cp1252_map)) );
}

/*
function uniord($c){
   $ud = 0;
   if (ord($c{0})>=0 && ord($c{0})<=127)  $ud = $c{0};
   if (ord($c{0})>=192 && ord($c{0})<=223) $ud = (ord($c{0})-192)*64 + (ord($c{1})-128);
   if (ord($c{0})>=224 && ord($c{0})<=239) $ud = (ord($c{0})-224)*4096 + (ord($c{1})-128)*64 + (ord($c{2})-128);
   if (ord($c{0})>=240 && ord($c{0})<=247) $ud = (ord($c{0})-240)*262144 + (ord($c{1})-128)*4096 + (ord($c{2})-128)*64 + (ord($c{3})-128);
   if (ord($c{0})>=248 && ord($c{0})<=251) $ud = (ord($c{0})-248)*16777216 + (ord($c{1})-128)*262144 + (ord($c{2})-128)*4096 + (ord($c{3})-128)*64 + (ord($c{4})-128);
   if (ord($c{0})>=252 && ord($c{0})<=253) $ud = (ord($c{0})-252)*1073741824 + (ord($c{1})-128)*16777216 + (ord($c{2})-128)*262144 + (ord($c{3})-128)*4096 + (ord($c{4})-128)*64 + (ord($c{5})-128);
   if (ord($c{0})>=254 && ord($c{0})<=255) $ud = false; //error
   return $ud;
}

function utf2iso($source) {
   global $utf2iso;
   $pos = 0;
   $len = strlen ($source);
   $encodedString = '';

   while ($pos < $len) {
       $is_ascii = false;
       $asciiPos = ord (substr ($source, $pos, 1));
       if(($asciiPos >= 240) && ($asciiPos <= 255)) {
           // 4 chars representing one unicode character
           $thisLetter = substr ($source, $pos, 4);
           $thisLetterOrd = uniord($thisLetter);
           $pos += 4;
       }
       else if(($asciiPos >= 224) && ($asciiPos <= 239)) {
           // 3 chars representing one unicode character
           $thisLetter = substr ($source, $pos, 3);
           $thisLetterOrd = uniord($thisLetter);
           $pos += 3;
       }
       else if(($asciiPos >= 192) && ($asciiPos <= 223)) {
           // 2 chars representing one unicode character
           $thisLetter = substr ($source, $pos, 2);
           $thisLetterOrd = uniord($thisLetter);
           $pos += 2;
       }
       else{
           // 1 char (lower ascii)
           $thisLetter = substr ($source, $pos, 1);
           $thisLetterOrd = uniord($thisLetter);
           $pos += 1;
           $encodedString .= $thisLetterOrd;
           $is_ascii = true;
       }
       if(!$is_ascii){
           $hex = sprintf("%X", $thisLetterOrd);
           if(strlen($hex)<4) for($t=strlen($hex);$t<4;$t++)$hex = "0".$hex;
           $hex = "0x".$hex;
           $hex = $utf2iso[$hex];
           $hex = str_replace('0x','',$hex);
           $dec = hexdec($hex);
           $encodedString .= sprintf("%c", $dec);
       }
   }
   return $encodedString;
}
*/
?>