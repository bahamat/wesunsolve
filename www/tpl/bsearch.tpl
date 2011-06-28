<?php 
      if ($start != 0 && $start >= $rpp) {
        $idprev = $start - $rpp;
      }
      if (($start + $rpp) >= $nb) {
        $idnext = $nb - 1;
      } else {
	$idnext = $start + $rpp;
      }
?>
 <div class="content">
  <h2>Search for bugids (<?php echo $rpp; ?> results out of <?php echo $nb; ?> starting from <?php echo $start; ?>)</h2>
  <div class="ctable">
  <table class="ctable">
  <tr><td></td><td colspan="5"><a href="<?php echo $str; ?>/start/0">&lt;&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;
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
    <?php if ($score) { ?><th>Score</th><?php } ?>
    <th>Bug ID</th>
    <th>Synopsis</th>
    <th>Created</th>
    <th>Updated</th>
    <th>Submitted</th>
    <th></th>
   </tr>
<?php foreach($bugids as $p) { ?>
   <tr>
    <?php if ($score) { ?><td><?php echo $p->score; ?></td><?php } ?>
    <td><?php echo $p->id; ?></td>
    <td><?php echo $p->synopsis; ?></td>
    <td><?php echo date('d/m/Y', $p->d_created); ?></td>
    <td><?php echo date('d/m/Y', $p->d_updated); ?></td>
    <td><?php echo date('d/m/Y', $p->d_submit); ?></td>
    <td><a href="/bugid/id/<?php echo $p->id; ?>">details</a></td>
   </tr>
<?php } ?>
  <tr><td></td><td colspan="5"><a href="<?php echo $str; ?>/start/0">&lt;&lt;</a>&nbsp;&nbsp;&nbsp;&nbsp;
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


