<?php

class object {
  var $serial;

	function object() {
		$this->serial = get_unique_object_serial();
	}

	function equals($that) {
		if(is_object($that) && $this->serial === $that->serial)
			return true;
		else
			return false;
	}
}

function get_unique_object_serial() {
	static $serial = 1;
	return $serial++;
}

?>