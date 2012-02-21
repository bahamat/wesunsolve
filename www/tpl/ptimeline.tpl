<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Patches timeline</h2>
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
  <div class="ctable">
  <table class="ctable">
   <tr>
    <th>Patch</th>
    <th>Details</th>
   </tr>
<?php
   $current = null;
   while(($pt = $pts->next())) {
     $pt->fetchFromId();
     $pt->fetchPatch();
     if (!$current || $current->id != $pt->id_patchdiag) {
       $current = new Patchdiag($pt->id_patchdiag);
       $current->fetchFromId();
       echo "<tr><td colspan=\"2\">Changes on ".date(HTTP::getDateFormat(), $pt->when)." inside <i><a href=\"/patchdiag/id/".$current->id."\">".$current->filename."</a> file</i><br/><br/></td></tr>\n";
     }
     ?>
     <tr><td><?php echo $pt->o_patch->link(false, true); ?></td><td><?php echo $pt->tell(); ?></td></tr>
     <?php
   }
?>
   </table>
  </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
