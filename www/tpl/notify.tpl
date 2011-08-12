<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Contact</h2>
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
  <?php if (isset($what)) { ?>
    <span class="red">Error, the <?php echo $what; ?> that you have requested has not been found.</span>
    <p>If the error persist, please fill the form below to notify the error!</p>
    <?php } else { ?>
    <p>You can contact us by:</p>
    <ul>
     <li><a href="irc://#sunsolve@irc.freenode.net">IRC</a> on <b>#sunsolve</b> @ <b>irc.freenode.org</b></li>
     <li>Twitter: <a href="http://twitter.com/wesunsolve">@WeSunSolve</a></li>
     <li><a href="mailto:info@wesunsolve.net">E-Mail</a></li>
     <li>With the form below...</li>
    </ul>
    <?php } ?>
    <form method="POST" action="notify/form/1">
    <table class="ctable">
      <tr><th>Your Name</th><td style="text-align: left;"><input type="text" value="" name="nom"></td></tr>
      <tr><th>Your E-mail</th><td style="text-align: left;"><input type="text" value="" name="email"></td></tr>
      <tr><th>Free Text</th><td style="text-align: left;"><textarea name="details"></textarea></td></tr>
      <tr><td></td><td style="text-align: left;"><input type="submit" value="Submit" name="submit"></td></tr>
    </table>
    </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
