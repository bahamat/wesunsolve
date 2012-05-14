<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">User groups</h2>
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
<?php if (isset($error)) { ?>
    <br/>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
  <h4>Groups owned by me (<a href="/add_ugroup">Add</a>) (<a href="http://wiki.wesunsolve.net/UserGroups">Documentation</a>)</h4>
    <p>You have <?php echo count($mgroups); ?> groups</p>
    <?php if (count($mgroups)) { ?>
    <table class="ctable">
     <tr>
      <th>Group Name</th>
      <th>Description</th>
      <th># Members</th>
      <th># Servers</th>
      <th></th>
      <th></th>
     </tr>
<?php $i=1; foreach($mgroups as $l) { ?>
     <tr class="<?php if ($i % 2) { echo "tdp"; } else { echo "tdup"; } ?>">
      <td><?php echo $l->name; ?></td>
      <td><?php echo $l->desc; ?></td>
      <td><?php echo $l->countUsers(); ?></td>
      <td><?php echo $l->countServers(); ?></td>
      <td style="text-align: center;"><a href="/del_ugroup/id/<?php echo $l->id; ?>">del</a></td>
      <td style="text-align: center;"><a href="/ugroup/id/<?php echo $l->id; ?>">manage</a></td>
     </tr>
<?php $i++; } ?>
    </table>
<?php  } ?>
<br/>
<h4>Groups where I'm listed (<a href="http://wiki.wesunsolve.net/UserGroups">Documentation</a>)</h4>
    <p>You have <?php echo count($ugroups); ?> groups</p>
    <?php if (count($ugroups)) { ?>
    <table class="ctable">
     <tr>
      <th>Group Name</th>
      <th>Description</th>
      <th># Members</th>
      <th># Servers</th>
      <th></th>
     </tr>
<?php $i=1; foreach($ugroups as $l) { ?>
     <tr class="<?php if ($i % 2) { echo "tdp"; } else { echo "tdup"; } ?>">
      <td><?php echo $l->name; ?></td>
      <td><?php echo $l->desc; ?></td>
      <td><?php echo $l->countUsers(); ?></td>
      <td><?php echo $l->countServers(); ?></td>
      <td style="text-align: center;"><a href="/mgroup/id/<?php echo $l->id; ?>">view</a></td>
     </tr>
<?php $i++; } ?>
    </table>
<?php  } ?>

   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
