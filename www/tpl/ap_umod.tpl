<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Edit a user</h2>
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
     <form method="POST" action="/ap_umod/i/<?php echo $l->id; ?>/form/1">
      <table class="ctable">
        <tr><th>Login:</th><td><?php echo $l->username; ?></td></tr>
        <tr><th>Password:</th><td><input style="width: 300px;" type="password" name="u_password"/></td></tr>
        <tr><th>Email</th><td><input type="text" style="width: 300px;" name="u_email" value="<?php echo $l->email; ?>"/></td></tr>
        <tr><th>Full Name</th><td><input type="text" name="u_fullname" style="width: 300px;" value="<?php echo $l->fullname; ?>"/></td></tr>
        <tr><th>is_admin</th><td><input type="checkbox" name="u_isadmin" <?php if ($l->is_admin) echo "checked"; ?> /></td></tr>
        <tr><th>is_enabled</th><td><input type="checkbox" name="u_enabled" <?php if ($l->is_enabled) echo "checked"; ?> /></td></tr>
        <tr><th>is_dl</th><td><input type="checkbox" name="u_dl" <?php if ($l->is_dl) echo "checked"; ?> /></td></tr>
        <tr><th>is_log</th><td><input type="checkbox" name="u_log" <?php if ($l->is_log) echo "checked"; ?> /></td></tr>
        <tr><td></td><td><input type="submit" class="submit" name="save" value="Save changes"/></td></tr>
      </table>
     </form>
     <p><a href="/ap_udel/id/<?php echo $l->id; ?>">Delete</a></p>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
