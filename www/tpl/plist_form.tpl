<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Patch List report</h2>
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
  <form method="post" action="/plist_report/form/1">
  <div class="ctable">
  <p>Paste below the list of patches using one of the available formats</p>
  <ul>
   <li><b>PCA</b>: Output of <span class="code">pca -l m</span></li>
   <li><b>Text</b>: One full patch number per line (e.g.: 123456-78)</li>
   <li><b>showrev</b>: Output of <span class="code">showrev -p</span></li>
  </ul>
  <table class="ctable">
    <tr><td><select name="format">
	      <option value="pca">PCA</option>
	      <option value="text">Text</option>
	      <option value="showrev">Showrev</option>
            </select></td></tr>
    <tr><td><textarea class="bigtxt" name="plist"></textarea></td></tr>
    <tr><td><input type="submit" value="search"/></td></tr>
  </table>
  </div>
  </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
