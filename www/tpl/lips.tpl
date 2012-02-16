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
     <h2 class="grid_10 push_1 alpha omega">Monitored IPS Repositories</h2>
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
  <table id="tbl_pkgs" class="ctable">
   <tr>
    <th>Name</th>
    <th>Publisher</th>
    <th># Pkgs</th>
    <th>Since</th>
    <th>Last Updated</th>
   </tr>
<?php $i=0; foreach($ips as $i) { ?>
   <tr>
    <td><?php echo $i->link(); ?></td>
    <td><?php echo $i->publisher; ?></td>
    <td><?php echo count($i->a_pkgs); ?></td>
    <td><?php echo date(HTTP::getDateFormat(), $i->added); ?></td>
    <td><?php echo date(HTTP::getDateFormat(), $i->updated); ?></td>
   </tr>
<?php $i++; } ?>
   </table>
  </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 --> 
 </div><!-- d_content -->
