<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Change your password</h2>
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
    <?php if (isset($error)) { ?><h2 class="red"><?php if (isset($error) && !empty($error)) echo $error; ?></h2><?php } ?>
     <form method="POST" action="/password">
      <table class="ctable">
        <tr><th>Login:</th><td><?php echo $login; ?></td></tr>
        <tr><th>New password:</th><td><input class="field" type="password" name="password"/></td></tr>
        <tr><th>Confirmation:</th><td><input class="field" type="password" name="password2"/></td></tr>
        <tr><td></td><td><input type="submit" class="submit" name="save" value="Save changes"/></td></tr>
      </table>
     </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
