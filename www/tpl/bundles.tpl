<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Patch bundles</h2>
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
    <th>Synopsis</th>
    <th>Size</th>
    <th>Last Updated</th>
    <th></th>
   </tr>
<?php $i=1; foreach($bundles as $b) { ?>
   <tr class="<?php if ($i % 2) { echo "tdp"; } else { echo "tdup"; } ?>">
    <td style="text-align: left;"><?php echo $b->synopsis; ?></td>
    <td><?php echo round($b->size / 1024 / 1024, 2); ?> MBytes</td>
    <td><?php echo date(HTTP::getDateFormat(), $b->lastmod); ?></td>
    <td><a href="bundle/id/<?php echo $b->id; ?>">details</a></td>
   </tr>
<?php $i++; } ?>
   </table>
  </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
