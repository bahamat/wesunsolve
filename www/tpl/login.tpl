<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Please authenticate yourself!</h2>
     <div class="clear"></div>
     <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?> alpha omega">
      <div class="d_content_box">
      <div tyle="height: 30px" lass="push_<?php echo $h->css->p_snet; ?> grid_<?php echo $h->css->s_snet; ?>">
        <div class="addthis_toolbox addthis_default_style" id="snet">
         <a class="addthis_button_facebook"></a>
         <a class="addthis_button_twitter"></a>
         <a class="addthis_button_email"></a>
         <a class="addthis_button_print"></a>
         <a class="addthis_button_google_plusone"></a>
        </div>
       </div>
       <div class="clear clearfix"></div>
<?php if (isset($error)) { ?>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
    <form method="POST" action="/login">
    <table class="ctable">
      <tr><td>Login</td><td><input type="text" value="" name="username"></td></tr>
      <tr><td>Password</td><td><input type="password" value="" name="password"></td></tr>
      <tr><td></td><td><input id="cb_keeploggedin" type="checkbox" value="1" name="keep"><label for="cb_keeploggedin"> Keep me loggedin</label></td></tr>
      <tr><td></td><td><input type="submit" value="Login" name="save"></td></tr>
    </table>
    </form>
    <p><a href="/forgetpass">Forget your password?</a></p>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
