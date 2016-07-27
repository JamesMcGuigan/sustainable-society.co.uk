<?php

function swap(&$var1,&$var2) {
  $temp = &$var1;
  $var1 = &$var2;
  $var2 = &$temp;
}

?>