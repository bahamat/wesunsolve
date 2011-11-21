<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">File Search</h2>
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
  <p>Search files using a string or a MD5/SHA1 checksum, we'll try to find to which release or patch it correspond...</p>
  <form method="post" action="/fsearch/form/1">
  <div class="ctable">
  <table class="ctable">
    <tr><th>File pattern:</th><td><input type="text" name="fpa"/></td></tr>
    <tr><th>MD5:</th><td><input type="text" name="md5"/></td></tr>
    <tr><th>SHA1:</th><td><input type="text" name="sha1"/></td></tr>
    <tr><td></td><td><input type="submit" value="search"/></td></tr>
  </table>
  </div>
  </form>
  <br/>
  <p>NOTE: The search will be done against our database of patches and releases. <br/>
     You can check which releases are indexed on our <a href="/releases">referenced releases</a> page.<br/>
     Also, use the full path of file that you want to search. (/usr/bin/cp for cp)</p>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
