 <div class="content">
  <h2>Search for patches</h2>
  <p>The search is limited to the first 50 results, it will allow more soon...</p>
  <div class="ctable">
  <table class="ctable">
   <tr>
    <?php if ($score) { ?><th>Score</th><?php } ?>
    <th>Patch ID</th>
    <th>Release date</th>
    <th>Synopsis</th>
   </tr>
<?php foreach($patches as $p) { ?>
   <tr>
    <?php if ($score) { ?><td><?php echo $p->score; ?></td><?php } ?>
    <td <?php echo $p->color(); ?>><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a></td>
    <td><?php echo date('d/m/Y', $p->releasedate); ?></td>
    <td style="text-align: left;"><?php echo $p->synopsis; ?></td>
   </tr>
<?php } ?>
   </table>
  <p>Other search fields will come soon...</p>
  </div>
 </div>


