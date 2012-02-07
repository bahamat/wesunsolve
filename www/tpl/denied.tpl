<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Your access has been denied!</h2>
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
       <h3>Might be because of one of following reason:</h3>
       <ul class="listinfo">
	<li>You're requesting a page which require to <a href="/login">login</a> and you are not.</li>
	<li>You're using wget</li>
	<li>You mass-downloaded the website.</li>
	<li>You're automatically grabbing content. <a href="/docs#API">Check our API for that.</a></li>
       </ul>
       <br/>
       <p>You can still <a href="/contact">contact us</a> to discuss about this issue...</p>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
