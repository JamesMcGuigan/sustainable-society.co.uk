<?php

$contenttype = "application/x-octet-stream";
$directory   = preg_replace('%(.*)/[^/]*%','$1',$_SERVER['PHP_SELF']);	

if($_GET['playlist'] != '') {
	$m3ufilename = $_GET['playlist'].'.m3u';	
	$playlist = "$_GET[playlist]*.mp3";
	$mp3urls = explode("\n",`find $playlist`);
	if(count($mp3urls) == 0) { echo "<HTML><BODY><B>Playlist $_GET[playlist] does not exist!</B></BODY></HTML>\n"; exit(); } 
	foreach($mp3urls as $mp3) {
		if($mp3 != '') { $mp3url .= "http://$_SERVER[SERVER_NAME]$directory/$mp3\n"; }
	}
} 
else 
{
	$mp3file = $_SERVER['QUERY_STRING'];
	$mp3url = "http://$_SERVER[SERVER_NAME]$directory/$mp3file\n";
	$m3ufilename = preg_replace('%(.*)\..*%','$1.m3u',$mp3file);
	
	if (!file_exists($mp3file)) {
		echo "<HTML><BODY><B>File $mp3file does not exist!</B></BODY></HTML>\n";
		exit();
	}
}


Header("Content-Type: $contenttype");
Header("Content-Length: ".strlen($mp3url));
Header("Content-Disposition: attachment; filename='$m3ufilename'");

echo $mp3url;

?>

