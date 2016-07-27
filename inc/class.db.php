<?php
class DB {
  var $host;
  var $db;
  var $user;
  var $pass;
  var $connection;

  function DB() {
    $this->user='shb1_024_3';
    $this->pass='rosemary';
    $this->host='mysql.worldfuturecouncil.org';
    $this->db='shb1_024_3';
    $this->connection = mysql_connect($this->host, $this->user, $this->pass);
    mysql_select_db($this->db);
    //register_shutdown_function(array(&$this,"closedb"));

		// hide vars
    $this->user='';
    $this->pass='';
    $this->host='';
    $this->db='';
  }

	function query($sql) {
    $result = mysql_query($sql, $this->connection)
	  or die ("<b>A fatal MySQL error occured</b>.\n<br />Query: \"" . $sql . "\"<br />\n<b>SQL Error: (" . mysql_errno() . ")</b> " . mysql_error() )
		;

    $GLOBALS['SQL'][] = str_replace(array('\n',';'),array('\n<br>',';<br>'),$sql)."<br>";
    if($_GET['vars']==='show') { echo $GLOBALS['SQL']; }

    return $result;
  }

	function single_array($sql) {
		$result = $this->query($sql);
	  $entry = mysql_fetch_assoc($result);
		return $entry;
	}

	function single_scalar($sql) {
		$result = $this->query($sql);
	  $entry = mysql_fetch_row($result);
		return $entry[0];
	}

	function single_column($sql) {
		$return = array();
		$result = $this->query($sql);
		while($entry = mysql_fetch_row($result)) {
			$return[] = $entry[0];
		}
		return $return;
	}

	function multi_array($sql) {
		$return = array();
		$result = $this->query($sql);
		while($entry = mysql_fetch_assoc($result)) {
			$return[] = $entry;
		}
		return $return;
	}

  function closedb() {
    mysql_close($this->connection);
  }
};

$db = new DB;

class null_db {
  function query($sql) {
    return null;
  }

  function single_array($sql) {
    return array();
  }

  function single_scalar($sql) {
    return null;
  }

  function single_column($sql) {
    return array();  }

  function multi_array($sql) {
    return array(array());
  }

  function closedb() {
  }
}

?>
