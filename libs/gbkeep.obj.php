<?php

class gbKeep {

  private function destroy(&$var) {
    if (is_array($var)) {
      foreach ($var as $name => $v) {
	$this->destroy(&$v);
        unset($v);
      }
    } else if (is_object($var)) {
      foreach(get_object_vars($var) as $name => $v) {
        $this->destroy(&$v->{$name});
      }
    }
    unset($var);
  }

  function __destruct() {
  //  $s = memory_get_usage();
//    foreach(get_object_vars($this) as $name => $var) {
 //     $this->destroy(&$obj->{$name});
  //  }
   // $ret = gc_collect_cycles();
 //   $e = memory_get_usage();
//    echo "[DESTROY]: ".get_class($this)." ($ret) ($e) saved ".($s - $e)." bytes of memory\n";
  }
}
