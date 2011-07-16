   <div class="content">
    <h4>Servers</h4>
    <p>You have <?php echo count($servers); ?> server registered</p>
    <table class="slist">
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
   </div>
