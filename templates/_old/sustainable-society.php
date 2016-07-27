<!-- InstanceBegin template="/templates/sustainable-society.xml.dwt" codeOutsideHTMLIsLocked="false" --><!-- InstanceParam name="File" type="text" value="business.htm" -->
<!-- InstanceParam name="Layout" type="text" value="" -->
<!-- InstanceParam name="Quote" type="boolean" value="false" -->
<!-- InstanceParam name="Header" type="boolean" value="true" -->
<!-- InstanceParam name="BackToTop" type="boolean" value="true" -->

<!-- <?php // --> hides php tag from dreamweaver
echo '-->'; // close comment
ini_set(include_path,ini_get(include_path)
 . '://home/1/s/sh/shb1_024/sustainable-society.co.uk/public_html/ssphp'
 . '://home/1/s/sh/shb1_024/sustainable-society.co.uk/public_html/'
 . ':/home/james/websites/sustainable-society.co.uk/ssphp'
 . ':/home/james/websites/sustainable-society.co.uk');

function dw_doctitle() {
$title = <<<ENDOFHTML
<!-- InstanceBeginEditable name="doctitle" -->
<title>Sustainable Society Directory: <?php pagetitle(); ?></title>
<!-- InstanceEndEditable -->
ENDOFHTML;
if($title) $title = preg_replace('/<!--(.*?)-->|<title>|<\/title>/s','',$title);
else       $title = "Sustainable Society Directory";

echo "<title>$title</title>";
}

function dw_head() {
echo <<<ENDOFHTML
<!-- InstanceBeginEditable name="head" -->
<!-- InstanceEndEditable -->
ENDOFHTML;
}

function pagetitle() {
echo <<<ENDOFHTML
<!-- InstanceBeginEditable name="page-title" -->
<!-- InstanceEndEditable -->
ENDOFHTML;
}

function dw_quote() {
$quote = <<<ENDOFHTML
<!-- InstanceBeginEditable name="quote" -->
<!-- InstanceEndEditable -->
ENDOFHTML;
if($quote) echo $quote.$horizontalbar;
}

function content() {
echo <<<ENDOFHTML
<!-- InstanceBeginEditable name="content" -->

						<?php content(); ?>
      <!-- InstanceEndEditable -->
ENDOFHTML;
}

include_once('templates/sustainable-society.xml.php');

?><!-- InstanceEnd -->