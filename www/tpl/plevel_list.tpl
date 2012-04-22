<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Patch level</h2>
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
  <p>Patch level of <?php echo $s->name; ?></p>
    <p>You have <?php echo count($plevels); ?> different patch level</p>
    <table class="ctable">
     <tr>
      <th>Name</th>
      <th>Comment</th>
      <th># of Patches</th>
      <th></th>
      <th></th>
     </tr>
<?php foreach($plevels as $plevel) { ?>
     <tr>
      <td><?php echo $plevel->name; ?></td>
      <td><?php echo $plevel->comment; ?></td>
      <td style="text-align: center;"><?php echo count($plevel->a_patches); ?></td>
      <td style="text-align: center;"><a href="/plevel/s/<?php echo $s->id; ?>/p/<?php echo $plevel->id; ?>">View</a></td>
      <td style="text-align: center;"><a href="/del_plevel/s/<?php echo $s->id; ?>/p/<?php echo $plevel->id; ?>">Del</a></td>
     </tr>
<?php } ?>
    </table>
    <br/>
    <p><a href="/add_plevel/s/<?php echo $s->id; ?>">Add patch level</a></p>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
