<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Advanced patch search</h2>
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
  <form method="post" action="/psearch/form/1">
  <div class="ctable">
  <p>Search using a patch ID or a keyword using the fields below</p>
  <p>You can use % as a wildcard</p>
  <table class="ctable">
    <tr><th>Patch ID:</th><td><input type="text" name="pid"/></td></tr>
    <tr><th>Revision:</th><td><input type="text" name="rev"/></td></tr>
    <tr><th>Synopsis:</th><td><input type="text" name="synopsis"/></td></tr>
    <tr><th>Status:</th><td><input type="text" name="status"/></td></tr>
    <tr><th>File:</th><td><input type="text" name="files"/></td></tr>
    <tr><th>Package:</th><td><input type="text" name="pkg"/></td></tr>
<!--
    <tr><th>*Keyword:</th><td><input type="text" name="keyword"/></td></tr>
    <tr><th>*Architecture:</th><td><input type="text" name="arch"/></td></tr>
    <tr><th>*Solaris release: </th><td><input type="text" name="sol_release"/></td></tr>
    <tr><th>*SunOS release:</th><td><input type="text" name="sun_release"/></td></tr>
    <tr><th>*Unbundled product:</th><td><input type="text" name="un_product"/></td></tr>
    <tr><th>*Unbundled release:</th><td><input type="text" name="un_release"/></td></tr>
    <tr><th>*Patches that obsolete:</th><td><input type="text" name="obso"/></td></tr>
    <tr><th>*Patches in conflicts with:</th><td><input type="text" name="conflicts"/></td></tr>
    <tr><th>*Patches that requires:</th><td><input type="text" name="requires"/></td></tr>
    <tr><td colspan="2">Fields marked of a * are currently in construction and thus NOT working</td></tr>
-->
    <tr><td></td><td><input type="submit" value="search"/></td></tr>
  </table>
  </div>
  </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
