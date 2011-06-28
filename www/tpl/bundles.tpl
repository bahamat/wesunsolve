<div class="content">
  <h2>Patch Bundles</h2>
  <div class="ctable">
  <table class="ctable">
   <tr>
    <th>Synopsis</th>
    <th>Filename</th>
    <th>Size</th>
    <th>Last Updated</th>
    <th>Details</th>
   </tr>
<?php foreach($bundles as $b) { ?>
   <tr>
    <td style="text-align: left;"><?php echo $b->synopsis; ?></td>
    <td style="text-align: left;"><?php echo $b->filename; ?></td>
    <td><?php echo round($b->size / 1024 / 1024, 2); ?> MBytes</td>
    <td><?php echo date('d/m/Y', $b->lastmod); ?></td>
    <td><a href="bundle/id/<?php echo $b->id; ?>">click</a></td>
   </tr>
<?php } ?>
   </table>
  </div>
 </div>


