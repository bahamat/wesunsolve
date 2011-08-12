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
     <h2 class="grid_10 push_1 alpha omega">Search for bugids</h2>
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
  <p>Search for bugids (<?php echo $rpp; ?> results out of <?php echo $nb; ?> starting from <?php echo $start; ?>)</p>
  <div class="ctable">
  <p class="paging"><?php echo $pagination; ?></p>
  <table class="ctable">
   <tr>
    <?php if ($score) { ?><th>Score</th><?php } ?>
    <th>Bug ID</th>
    <th>Synopsis</th>
    <th>Created</th>
    <th>Updated</th>
    <th>Submitted</th>
   </tr>
<?php foreach($bugids as $p) { ?>
   <tr>
    <?php if ($score) { ?><td><?php echo $p->score; ?></td><?php } ?>
    <td><a href="/bugid/id/<?php echo $p->id; ?>"><?php echo $p->id; ?></a></td>
    <td><?php echo substr($p->synopsis,0,$h->css->s_strip - 10); ?></td>
    <td><?php echo date(HTTP::getDateFormat(), $p->d_created); ?></td>
    <td><?php echo date(HTTP::getDateFormat(), $p->d_updated); ?></td>
    <td><?php echo date(HTTP::getDateFormat(), $p->d_submit); ?></td>
   </tr>
<?php } ?>
   </table>
  <p class="paging"><?php echo $pagination; ?></p>
  </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
