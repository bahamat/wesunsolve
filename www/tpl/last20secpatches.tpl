<div class="content">
  <h2>Last 20 Security Patches registered</h2>
  <div class="ctable">
   <table class="ctable"><tr><td class="greentd">RECOMMENDED</td><td class="orangetd">SECURITY</td><td class="redtd">WITHDRAWN</td></tr></table>
  <table class="ctable">
   <tr>
    <th>Patch ID</th>
    <th>Release date</th>
    <th>Synopsis</th>
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


