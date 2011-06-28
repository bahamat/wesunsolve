<?php

function getData($obj, $arg) {

  if (is_array($arg)) {
    $first = $arg[0];
    $newarg = array();
    if (count($arg) == 2) {
      $newarg = $arg[1];
    } else {
      for ($i=1,$j=0; $i<count($arg); $i++,$j++) {
        $newarg[$j] = $arg[$i];
      }
    }
    if (strstr($first, "()")) { /* need to call function */
      if (is_array($newarg)) {
        return call_user_func_array(array($obj, str_replace("()", "", $first)), $newarg);
      } else {
        return call_user_func(array($obj, str_replace("()", "", $first)), $newarg);
      }
    } else {
      return getData($obj->{$first}, $newarg);
    }
  } else {
    return $obj->{$arg};
  }
}

?>
