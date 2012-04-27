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
     <h2 class="grid_10 push_1 alpha omega">User list</h2>
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
  <table id="tbl_patches" class="ctable">
   <tr>
    <th>Username</th>
    <th>Fullname</th>
    <th>Email</th>
    <th>Added</th>
    <th>Last seen</th>
    <th>E</th>
    <th>A</th>
    <th>L</th>
    <th>API</th>
    <th></th>
   </tr>
<?php $i=0; foreach($logins as $l) { $l->fetchData(); ?>
   <tr class="<?php if ($i % 2) { echo "tdp"; } else { echo "tdup"; } ?>">
    <td style="text-align: left"><a href="/ap_umod/i/<?php echo $l->id; ?>"><?php echo $l->username; ?></a></td>
    <td style="text-align: left"><?php echo $l->fullname; ?></td>
    <td style="text-align: left"><?php echo $l->email; ?></td>
    <td style="text-align: left"><?php echo date(HTTP::getDateFormat(), $l->added); ?></td>
    <td style="text-align: left"><?php echo date(HTTP::getDateFormat(), $l->last_seen); ?></td>
    <td style="text-align: left"><?php echo HTTP::eval_img($l->is_enabled); ?></td>
    <td style="text-align: left"><?php echo HTTP::eval_img($l->is_admin); ?></td>
    <td style="text-align: left"><?php echo HTTP::eval_img($l->is_log); ?></td>
    <td style="text-align: left"><?php echo HTTP::eval_img($l->data("apiAccess")); ?></td>
    <td style="text-align: left"></td>
   </tr>
<?php $i++; } ?>
   </table>
  <p class="paging"><?php echo $pagination; ?></p>
  </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 --> 
 </div><!-- d_content -->
