<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Manage your mailling subscription</h2>
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
     <form method="POST" action="/mlist">
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
	  <td><input type="checkbox" name="ml[<?php echo $mlist->id; ?>]" value="1" <?php if ($lo->isMList($mlist)) echo "checked"; ?>/></td>
	</tr>
<?php } ?>
        <tr><td>&nbsp;</td></tr>
        <tr><td style="text-align: right;" colspan="5"><input type="submit" class="submit" name="save" value="Save changes"/></td></tr>
      </table>
     </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
