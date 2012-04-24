<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Register on We Sun Solve!</h2>
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
<?php if (isset($error)) { ?>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
    <p>Please enter a valid e-mail address, a confirmation e-mail will be sent to it.</p>
    <p>You can check the documentation on the <a href="http://wiki.wesunsolve.net/RegistrationProcess">registration process</a>.</p>
    <form method="POST" action="/register">
    <table class="ctable">
      <tr><td>Full Name</td><td><input type="text" value="<?php if (isset($fullname)) echo $fullname; ?>" name="fullname"></td></tr>
      <tr><td>Email</td><td><input type="text" value="<?php if (isset($email)) echo $email; ?>" name="email"></td></tr>
      <tr><td>Login</td><td><input type="text" value="<?php if (isset($username)) echo $username; ?>" name="username"></td></tr>
      <tr><td>Password</td><td><input type="password" value="<?php if (isset($password)) echo $password; ?>" name="password"></td></tr>
      <tr><td>Confirmation</td><td><input type="password" value="<?php if (isset($password2)) echo $password2; ?>" name="password2"></td></tr>
    </table> <p>Mailling report subscriptions:</p>
      <table class="ctable">
        <tr>
          <th>Name</th>
          <th>Description</th>
          <th>Frequency</th>
          <th></th>
          <th>Subscribe?</th>
        </tr>
<?php foreach ($mlists as $mlist) { ?>
        <tr>
          <td><?php echo $mlist->name; ?></td>
          <td><?php echo $mlist->sdesc; ?></td>
          <td><?php echo $mlist->frequency; ?></td>
          <td><a href="/mlist_ex/id/<?php echo $mlist->id; ?>">Example</a></td>
          <td><input type="checkbox" name="ml[<?php echo $mlist->id; ?>]" value="1" checked /></td>
        </tr>
<?php } ?>
        <tr><td>&nbsp;</td></tr>
        <tr><td colspan="2" style="text-align: right;"><input type="submit" value="Register" name="save"></td></tr>
      </table>
    </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
