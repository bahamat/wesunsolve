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
     <h2 class="grid_10 push_1 alpha omega">Last Solaris 11 Pkgs</h2>
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
  <div class="ctable">
  <p class="paging"><?php echo $pagination; ?></p>
  <table id="tbl_pkgs" class="ctable">
   <tr>
    <th>Name</th>
    <th>Pkg FMRI</th>
    <th>Release date</th>
   </tr>
<?php $i=0; foreach($pkgs as $p) { ?>
   <tr>
    <td><?php if ($p->isNew()) { ?><img class="newimg" src="/img/new.png" alt="New"/> <?php } ?><a href="/pkg/id/<?php echo $p->id; ?>"><?php echo $p->name; ?></a></td>
    <td><?php echo $p->fmri; ?></td>
    <td><?php echo date(HTTP::getDateFormat(), $p->pstamp); ?></td>
   </tr>
<?php $i++; } ?>
   </table>
  <p class="paging"><?php echo $pagination; ?></p>
  </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 --> 
 </div><!-- d_content -->
