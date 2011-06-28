<?php
 /**
  * autoload.lib.php
  *
  * Autoload object libraries
  *
  * 2009 - Gouverneur Thomas
  * thomas.gouverneur@belgacom.be
  */

  function __autoload($name) {
    global $config;

    $name = strtolower($name);
    $file = $config['rootpath']."/libs/".$name.".obj.php";
    if (file_exists($file)) {
      require_once($file);
    } else {
      trigger_error("Cannot load $file...<br/>\n", E_USER_ERROR);
    }
  }
?>
