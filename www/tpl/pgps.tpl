<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">PGP Key Status</h2>
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
    <?php if (isset($pgpFp)) { ?>
      <h3>Your PGP fingerprint</h3>
      <pre>
       <?php foreach($pgpFp as $l) echo "$l\n"; ?>
      </pre>
    <?php } ?>
    <?php if (isset($badFP)) { ?>
      <h3>Bad PGP fingerprint found</h3>
      <pre>
       <?php foreach($badFP as $l) echo "$l\n"; ?>
      </pre>
    <?php } ?>


   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
