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
  <p>Warning: The patch synopsis is limited to 100 char on this array, please check patch detail to see full messages</p>
  <h3>Eligible patches to be installed as of <?php echo $pdiag; ?></h3>
  <div class="ctable">
  <table class="ctable"><tr><td class="greentd">RECOMMENDED</td><td class="orangetd">SECURITY</td><td class="redtd">WITHDRAWN</td></tr></table>
  <table class="ctable">
    <tr>
      <th>Patch</th>
      <th>Released</th>
      <th>Installed</th>
      <th>Synopsis</th>
    </tr>
<?php foreach($pl->a_ppatches as $p) { ?>
    <tr>
      <td <?php echo $p->color(); ?>><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a></td>
      <td><?php if ($p->releasedate) echo date(HTTP::getDateFormat(), $p->releasedate); ?></td>
<?php if ($p->o_current) { ?>
      <td><a href="/patch/id/<?php echo $p->o_current->name(); ?>"><?php echo $p->o_current->name(); ?></a></td>
<?php } else { ?>
      <td>None</td>
<?php } ?>
      <td style="text-align: left;"><?php echo substr($p->synopsis,0,$h->css->s_strip); ?></td>
    </tr>
<?php } ?>
  </table>
  </div>
  <h3>CVE Fixed by eligible patches</h3>
  <div class="ctable">
  <table id="tbl_cves" class="ctable">
   <tr>
    <th>Patch</th>
    <th>Affected</th>
    <th>CVE</th>
    <th>Score</th>
    <th>Patch release date</th>
   </tr>
<?php $i=0; foreach($pl->a_pcvep as $p) { ?>
   <tr>
    <td <?php echo $p->color(); ?>><?php if ($p->isNew()) { ?><img class="newimg" src="/img/new.png" alt="New"/> <?php } echo $p->link(); ?></td>
    <td style="text-align: left"><?php echo $p->o_cve->affect; ?></td>
    <td <?php echo $p->o_cve->color(); ?>><?php echo $p->o_cve->link(); ?></td>
    <td><?php echo $p->o_cve->score; ?></td>
    <td><?php echo date(HTTP::getDateFormat(), $p->releasedate); ?></td>
   </tr>
<?php $i++; } ?>
   </table>
  </div>
   </div><!-- d_content_box -->
 </div><!-- d_content -->
