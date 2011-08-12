 <?php
      if ($start != 0 && $start >= $rpp) {
        $idprev = $start - $rpp;
      }
      if (($start + $rpp) >= $nb) {
        $idnext = $nb - 1;
      } else {
        $idnext = $start + $rpp;
      }
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Patch search results</h2>
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
  <p>Search for patches (<?php echo $rpp; ?> results out of <?php echo $nb; ?> starting from <?php echo $start; ?>)</p>
  <div class="ctable">
  <p class="paging"><?php echo $pagination; ?></p>
  <table class="ctable">
   <tr>
    <?php if ($score) { ?><th>Score</th><?php } ?>
    <th>Patch ID</th>
    <th>Release date</th>
    <th>Synopsis</th>
   </tr>
<?php foreach($patches as $p) { ?>
   <tr>
    <?php if ($score) { ?><td><?php echo $p->score; ?></td><?php } ?>
    <td <?php echo $p->color(); ?>><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a></td>
    <td><?php if (!$p->releasedate) echo "undef"; else echo date(HTTP::getDateFormat(), $p->releasedate); ?></td>
    <td style="text-align: left;"><?php echo substr($p->synopsis, 0, $h->css->s_strip); ?></td>
   </tr>
<?php } ?>
   </table>
  <p class="paging"><?php echo $pagination; ?></p>
  </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
