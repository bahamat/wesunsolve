<?php 
      if ($start != 0 && $start >= $rpp) {
        $idprev = $start - $rpp;
      }
      if (($start + 50) > $nb) {
        $idnext = $nb;
      } else {
        $idnext = $start + $rpp;
      }
?>
<div class="content">
  <h2>Last Patches registered (<?php echo $rpp; ?> patches of <?php echo $nb; ?> starting from <?php echo $start; ?>)</h2>
  <div class="ctable">
   <table class="ctable"><tr>
				<td class="greentd">RECOMMENDED</td>
				<td class="orangetd">SECURITY</td>
				<td class="redtd">WITHDRAWN</td>
				<td class="browntd">OBSOLETE</td>
			</tr></table>
  <table class="ctable">
 <tr><td colspan="3"><a href="/last20patches/start/0">&lt;&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (isset($idprev)) { ?>
      <a href="<?php echo $str; ?>/start/<?php echo $idprev; ?>">[prev]</a>
<?php } else { ?>
      [prev]
<?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (isset($idnext)) { ?>
      <a href="<?php echo $str; ?>/start/<?php echo $idnext; ?>">[next]</a>
<?php } else { ?>
      [next]
<?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="<?php echo $str; ?>/start/<?php echo $nb-$rpp; ?>">&gt;&gt;</a></td></tr>
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
 <tr><td colspan="3"><a href="<?php echo $str; ?>/start/0">&lt;&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (isset($idprev)) { ?>
      <a href="<?php echo $str; ?>/start/<?php echo $idprev; ?>">[prev]</a>
<?php } else { ?>
      [prev]
<?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;
<?php if (isset($idnext)) { ?>
      <a href="<?php echo $str; ?>/start/<?php echo $idnext; ?>">[next]</a>
<?php } else { ?>
      [next]
<?php } ?>&nbsp;&nbsp;&nbsp;&nbsp;
  <a href="<?php echo $str; ?>/start/<?php echo $nb-$rpp; ?>">&gt;&gt;</a></td></tr>
   </table>
  </div>
 </div>


