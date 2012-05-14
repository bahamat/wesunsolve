<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">User group <?php echo $mgroup->name; ?></h2>
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
    <p class="red"><?php if (isset($error) && !empty($error)) echo $error; ?></p>
    <?php if (isset($msg)) { ?><p><?php echo $msg; ?></p><?php } ?>
    <p>There are <?php echo count($mgroup->a_users); ?> user(s) in this group</p>
    <p>There are <?php echo count($mgroup->a_srv); ?> server(s) in this group</p>
   <h4>Servers access for the group</h4>
   <p>You can find below the list of servers which the group has access to.</p>
   <table class="ctable">
    <tr>
      <th>Server name</th>
      <th># of Patch level</th>
      <th>Write?</th>
      <th>View Patching level</th>
      <th>Add Patching level</th>
    </tr>
<?php foreach($mgroup->a_srv as $p) { ?>
    <tr>
      <td><?php echo $p; ?></td>
      <td style="text-align: center;"><?php echo $p->countPLevels(); ?></td>
      <td><?php echo HTTP::eval_img($p->w); ?></td>
      <td style="text-align: center;"><a href="/plevel/s/<?php echo $p->id; ?>">View</a></td>
      <td style="text-align: center;"><a href="/add_plevel/s/<?php echo $p->id; ?>">Add</a></td>
    </tr>
<?php } ?>
   </table>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
