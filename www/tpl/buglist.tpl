<div class="content">
  <h2>Bug list</h2>
  <div class="ctable">
  <table class="ctable">
   <tr>
    <th>Bug ID</th>
    <th>Synopsis</th>
    <th>Details</th>
   </tr>
<?php foreach($patches as $p) { ?>
   <tr>
    <td <?php echo $p->color(); ?>><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a></td>
    <td><?php echo date('d/m/Y', $p->releasedate); ?></td>
    <td style="text-align: left"><?php echo $p->synopsis; ?></td>
   </tr>
<?php } ?>
   </table>
  </div>
 </div>


