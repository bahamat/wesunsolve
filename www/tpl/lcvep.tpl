<?php 
      if ($start != 0 && $start >= $rpp) {
        $idprev = $start - $rpp;
      }
      if (($start + 50) >= $nb) {
        $idnext = $nb - 1;
      } else {
        $idnext = $start + $rpp;
      }
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Latest patches fixing security alerts</h2>
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
  <p><?php echo $title; ?></p>
  <p>Check the <a href="http://wiki.wesunsolve.net/LastCVE">documentation</a>.</p>
  <div class="ctable">
   <table id="legend" class="ctable">
			<tr><td colspan="4">Patch color legend
			</td></tr>
			<tr>
                                <td class="greentd">RECOMMENDED</td>
                                <td class="orangetd">SECURITY</td>
                                <td class="redtd">WITHDRAWN</td>
                                <td class="browntd">OBSOLETE</td>
                        </tr></table>
   <table id="legend" class="ctable">
		        <tr><td colspan="3">CVE color legend
			</td></tr>
		        <tr>
                                <td class="greentd">LOW</td>
                                <td class="orangetd">MEDIUM</td>
                                <td class="redtd">HIGH</td>
                        </tr></table>
  <p class="paging"><?php echo $pagination; ?></p>
  <table id="tbl_cves" class="ctable">
   <tr>
    <th>Patch</th>
    <th>Affected</th>
    <th>CVE</th>
    <th>Score</th>
    <th>Patch release date</th>
   </tr>
<?php $i=0; foreach($patches as $p) { ?>
   <tr>
    <td <?php echo $p->color(); ?>><?php if ($p->isNew()) { ?><img class="newimg" src="/img/new.png" alt="New"/> <?php } echo $p->link(); ?></td>
    <td style="text-align: left"><?php echo $p->o_cve->affect; ?></td>
    <td <?php echo $p->o_cve->color(); ?>><?php echo $p->o_cve->link(); ?></td>
    <td><?php echo $p->o_cve->score; ?></td>
    <td><?php echo date(HTTP::getDateFormat(), $p->releasedate); ?></td>
   </tr>
<?php $i++; } ?>
   </table>
  <p class="paging"><?php echo $pagination; ?></p>
  </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 --> 
 </div><!-- d_content -->
