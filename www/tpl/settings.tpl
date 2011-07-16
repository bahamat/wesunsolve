   <div class="content">
    <h4>Change your settings</h4>
    <?php if (isset($error)) { ?><h2 class="red"><?php if (isset($error) && !empty($error)) echo $error; ?></h2><?php } ?>
    <?php if (isset($msg)) { ?><h2 class="red"><?php if (isset($msg) && !empty($msg)) echo $msg; ?></h2><?php } ?>
     <form method="POST" action="/settings">
      <table class="ctable">
        <tr><th>Login:</th><td><?php echo $login; ?></td></tr>
<?php 
  foreach ($lo->_plist as $name => $param) {
    $desc = $param['desc'];
    switch ($param['type']) {
      case "N": // Numeric settings
        $min = $param['min'];
        $max = $param['max'];
	$val = $lo->data($name);
	global $config;
        /* if val is not populated, try default value... */
        if (!$val && isset($config[$name])) {
	  $val = $config[$name];
	}
        echo "<tr><th>$desc:</th><td><input class=\"field\" name=\"$name\" value=\"$val\" type=\"text\"/>($min < n < $max)</td></tr>\n";
      default:
	continue;
      break;
    }
  }
?>
        <tr><td></td><td><input type="submit" class="submit" name="save" value="Save changes"/></td></tr>
      </table>
     </form>
   </div>
