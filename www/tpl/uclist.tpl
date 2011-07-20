   <div class="content">
    <h4>User List <?php echo $uclist->name; ?></h4>
    <p>There is <?php echo count($uclist->a_patches); ?> patches in this list</p>
  <table class="ctable"><tr><td class="greentd">RECOMMENDED</td><td class="orangetd">SECURITY</td><td class="redtd">WITHDRAWN</td></tr></table>
  <table class="ctable">
    <tr>
      <th>Patch</th>
      <th>Released</th>
      <th>Latest?</th>
      <th>Status</th>
      <th>Synopsis</th>
      <th>Readme</th>
      <th></th>
    </tr>
<?php foreach($uclist->a_patches as $p) { ?>
    <tr>
      <td <?php echo $p->color(); ?>><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a></td>
      <td><?php if ($p->releasedate) echo date('d/m/Y', $p->releasedate); ?></td>
      <td><?php if ($p->o_latest === false) { echo "yes"; } else if ($p->o_latest) { echo "<a href=\"/patch/id/".$p->o_latest->name()."\">".$p->o_latest->name()."</a>"; } else { echo "not found"; } ?></td> 
      <td><?php echo $p->status; ?></td>
      <td style="text-align: left;"><?php echo substr($p->synopsis,0,100); ?></td>
      <td><a href="/readme/id/<?php echo $p->name(); ?>">Readme</a></td>
      <td id="p_<?php echo $p->name(); ?>"><a onClick="delUCList('<?php echo $p->name(); ?>', '<?php echo $uclist->id; ?>', showDelMsg);" href="#">Remove</a></td>
    </tr>
<?php } ?>
   </table>

   </div>
