<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">User panel</h2>
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
   <?php if (isset($lvp)) { ?>
   <div class="listbox grid_<?php echo $h->css->s_box; ?> firstbox alpha">
    <h3>Last viewed patches</h3>
    <ul>
     <?php foreach($lvp as $p) { ?>
     <li><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a></li>
     <?php } ?>
    </ul>
   </div>
   <?php } ?>
   <?php if (isset($lvb)) { ?>
   <div class="listbox grid_5 omega">
    <h3>Last viewed bugs</h3>
    <ul>
     <?php foreach($lvb as $b) { ?>
     <li><a href="/bugid/id/<?php echo $b->id; ?>"><?php echo $b->id; ?></a></li>
     <?php } ?>
    </ul>
   </div>
   <div class="clear"></div>
   <?php } ?>
   <div class="clear"></div>

  <h4>Custom Lists</h4>
    <p>You have <?php echo count($uclists); ?> custom list of patches</p>
    <?php if (count($uclists)) { ?>
    <table class="ctable">
     <tr>
      <th>List Name</th>
      <th># of Patchs</th>
      <th></th>
      <th></th>
     </tr>
<?php foreach($uclists as $l) { ?>
     <tr>
      <td><?php echo $l->name; ?></td>
      <td style="text-align: center;"><?php echo count($l->a_patches); ?></td>
      <td style="text-align: center;"><a href="/uclist/i/<?php echo $l->id; ?>">View</a></td>
      <td style="text-align: center;"><a href="/del_uclist/i/<?php echo $l->id; ?>">Del</a></td>
     </tr>
<?php } ?>
    </table>
<?php  } ?>
    <h4>Servers</h4>
    <p>You have <?php echo count($servers); ?> server registered</p>
    <table class="ctable">
     <tr>
      <th>Server Name</th>
      <th>Comment</th>
      <th># of Patch level</th>
      <th>View Patching level</th>
      <th>Add Patching level</th>
     </tr>
<?php foreach($servers as $srv) { ?>
     <tr>
      <td><?php echo $srv->name; ?></td>
      <td><?php echo $srv->comment; ?></td>
      <td style="text-align: center;"><?php echo count($srv->a_plevel); ?></td>
      <td style="text-align: center;"><a href="/plevel/s/<?php echo $srv->id; ?>">View</a></td>
      <td style="text-align: center;"><a href="/add_plevel/s/<?php echo $srv->id; ?>">Add</a></td>
     </tr>
<?php } ?>
    </table>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
