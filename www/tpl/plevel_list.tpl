   <div class="content">
    <h4>Patch level of <?php echo $s->name; ?></h4>
    <p>You have <?php echo count($plevels); ?> different patch level</p>
    <table class="slist">
     <tr>
      <th>Name</th>
      <th>Comment</th>
      <th># of Patches</th>
      <th>View Patching level</th>
      <th>Delete Patching level</th>
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
    <p><a href="/add_plevel/s/<?php echo $s->id; ?>">Add patch level</a></p>
    <hr/>
     <address></address>
   </div>
