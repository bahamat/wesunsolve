<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Change your settings</h2>
     <div class="clear"></div>
     <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?> alpha omega">
      <div class="d_content_box">
      <div style="height: 30px" class="push_<?php echo $h->css->p_snet; ?> grid_<?php echo $h->css->s_snet; ?>">
        <div class="addthis_toolbox addthis_default_style" id="snet">
         <a class="addthis_button_facebook"></a>
         <a class="addthis_button_twitter"></a>
         <a class="addthis_button_email"></a>
         <a class="addthis_button_print"></a>
         <a class="addthis_button_google_plusone"></a>
        </div>
       </div>
       <div class="clear clearfix"></div>
    <?php if (isset($error)) { ?><p class="red"><?php if (isset($error) && !empty($error)) echo $error; ?></p><?php } ?>
    <?php if (isset($msg)) { ?><p class="red"><?php if (isset($msg) && !empty($msg)) echo $msg; ?></p><?php } ?>
<p>You can check the documentations about <a href="http://wiki.wesunsolve.net/SettingsManagement">Your settings</a>.</p>
     <form method="POST" action="/settings">
      <table class="ctable">
        <tr><th>Login:</th><td><?php echo $login; ?></td></tr>
<?php 
  foreach ($lo->_plist as $name => $param) {
    $desc = $param['desc'];
    switch ($param['type']) {
      case "E": // enum
        $val = $lo->data($name);
        global $config;
        /* if val is not populated, try default value... */
        if (!$val && isset($config[$name])) {
          $val = $config[$name];
        }
        echo "<tr><th>$desc:</th><td><select class=\"field\" name=\"$name\">";
        foreach($param['values'] as $na => $va) {
	  echo "<option value=\"$va\"";
          if ($va == $val) echo "selected";
	  echo ">$na</option>";
	}
	echo "</select>";
      break;
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
      break;
      case "B": // boolean settings
        if (isset($param['objvar'])) {
          $val = $lo->{$name};
	} else {
          $val = $lo->data($name);
	}
        global $config;
        /* if val is not populated, try default value... */
        if (!$val && isset($config[$name])) {
          $val = $config[$name];
        }
        if ($val) {
         $val = "checked";
        } else {
         $val = "";
        }
        echo "<tr><th>$desc:</th><td><input class=\"field\" name=\"$name\" value=\"1\" ".$val." type=\"checkbox\"/></td></tr>\n";

      break;
      default:
	continue;
      break;
    }
  }
?>
        <tr><td></td><td><input type="submit" class="submit" name="save" value="Save changes"/></td></tr>
      </table>
     </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
