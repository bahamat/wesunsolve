<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Patchdiag archive</h2>
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
    <p>Here, you can download old patchdiag.xref from a certain date. Please select the one you want inside the droplist below then click fetch.</p>
    <form method="POST" action="/patchdiag">
      <table class="ctable">
      <tr><td>File listing</td><td><select name="id">
				<option value="-1">Choose a patchdiag.xref file</option>
<?php if (isset($list)) {
        foreach($list as $pdiag) { ?>
				<option value="<?php echo $pdiag->id; ?>"><?php echo $pdiag->format(); ?></option>
<?php   }
      } ?>
			    </select></td></tr>
      <tr><td></td><td><input type="submit" value="fetch" name="fetch"></td></tr>
      </table>
    </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
