<?php
/**
 * Auto load of dependancies
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2009-2011, Gouverneur Thomas
 * @version 1.0
 * @package includes
 * @subpackage libraries
 * @category objects
 */

  /**
   * Load file dependancies when they are needed.
   * @return nothing
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
