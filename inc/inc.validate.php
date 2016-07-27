<?php

function validate_isbn($isbn) {
	// TODO implement
	return true;
}


function validate_datestring($datestring) {
	$timestamp = strtotime($datestring);
	$valid = ($timestamp === -1) ? true : false;
	return $valid;
}


/*
 * @return boolean
 * @param  string $url
 * @desc  Analysises and Validated an URL
 * @link  http://uk.php.net/function.fsockopen
 */
function validate_url($url)
{
  $url_parts = @parse_url($url);

  if (empty($url_parts["host"])) return(false);

  if (!empty($url_parts["path"])) {
    $documentpath = $url_parts["path"];
  } else {
    $documentpath = "/";
  }

  if (!empty($url_parts["query"])) {
    $documentpath .= "?" . $url_parts["query"];
  }

  $host = $url_parts["host"];
  $port = $url_parts["port"];
  // Now (HTTP-)GET $documentpath at $host";
  if (empty($port)) $port = "80";
  $socket = @fsockopen($host, $port, $errno, $errstr, 30);
  if (!$socket) {
    return(false);
  } else {
    fwrite ($socket, "HEAD " . $documentpath . " HTTP/1.0\r\nHost: $host\r\n\r\n");
    $http_response = fgets($socket, 22);

    // 20x = Success | 30x = Moved | 40x = BadLink | 50x ServerError
    if (preg_match("/[2-3]0\d/", $http_response, $regs) ) {
      return(true);
      fclose($socket);
    } else {
      // echo "HTTP-Response: $http_response<br>";
      return(false);
    }
  }
}


// @link http://www.developer.com/lang/php/article.php/10941_3290141_2
function validate_email($email)
{

   // Create the syntactical validation regular expression
   $regexp = "^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$";

   // Presume that the email is invalid
   $valid = 0;

   // Validate the syntax
   if (eregi($regexp, $email))
   {
      list($username,$domaintld) = split("@",$email);

      // Validate the domain
      if (getmxrr($domaintld,$mxrecords))
         $valid = 1;
			else
				 $valid = null; // === null to test for invalid mail domain
   } else {
      $valid = 0;
   }

   return $valid;

}

?>