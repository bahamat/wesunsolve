<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">User list <?php echo $uclist->name; ?></h2>
     <div class="clear"></div>
     <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?> alpha omega">
      <div class="d_content_box">
       <div style="height: 30px" class="push_<?php echo $h->css->p_snet; ?> grid_<?php echo $h->css->s_snet; ?> alpha omega">
        <div class="addthis_toolbox addthis_default_style" id="snet">
         <a class="addthis_button_facebook"></a>
         <a class="addthis_button_twitter"></a>
         <a class="addthis_button_email"></a>
         <a class="addthis_button_print"></a>
         <a class="addthis_button_google_plusone"></a>
        </div>
       </div>
       <div class="clear clearfix"></div>
    <p>There is <?php echo count($uclist->a_patches); ?> patches in this list</p>
  <table class="ctable"><tr><td class="greentd">RECOMMENDED</td><td class="orangetd">SECURITY</td><td class="redtd">WITHDRAWN</td></tr></table>
  <table class="ctable">
    <tr>
      <th>Patch</th>
      <th>Released</th>
      <th>Latest?</th>
      <th>Status</th>
      <th>Synopsis</th>
      <th>Readme</th>
      <th></th>
    </tr>
<?php foreach($uclist->a_patches as $p) { ?>
    <tr>
      <td <?php echo $p->color(); ?>><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a></td>
      <td><?php if ($p->releasedate) echo date(HTTP::getDateFormat(), $p->releasedate); ?></td>
      <td><?php if ($p->o_latest === false) { echo "yes"; } else if ($p->o_latest) { echo "<a href=\"/patch/id/".$p->o_latest->name()."\">".$p->o_latest->name()."</a>"; } else { echo "not found"; } ?></td> 
      <td><?php echo $p->status; ?></td>
      <td style="text-align: left;"><?php echo substr($p->synopsis,0,100); ?></td>
      <td><a href="/readme/id/<?php echo $p->name(); ?>">Readme</a></td>
      <td id="p_<?php echo $p->name(); ?>"><a onClick="delUCList('<?php echo $p->name(); ?>', '<?php echo $uclist->id; ?>', showDelMsg);" href="#">Remove</a></td>
    </tr>
<?php } ?>
   </table>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
