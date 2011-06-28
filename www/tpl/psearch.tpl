 <?php
      if ($start != 0 && $start >= $rpp) {
        $idprev = $start - $rpp;
      }
      if (($start + $rpp) > $nb) {
        $idnext = $nb;
      } else {
        $idnext = $start + $rpp;
      }
?>
<div class="content">
  <h2>Search for patches (<?php echo $rpp; ?> results out of <?php echo $nb; ?> starting from <?php echo $start; ?>)</h2>
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


