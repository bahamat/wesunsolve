<?php 
      if ($start != 0 && $start >= $rpp) {
        $idprev = $start - $rpp;
      }
      if (($start + 50) >= $nb) {
        $idnext = $nb - 1;
      } else {
        $idnext = $start + $rpp;
      }
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Last patches</h2>
     <div class="clear"></div>
     <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?> alpha omega">
      <div class="d_content_box">
       <div style="height: 30px" class="push_<?php echo $h->css->p_snet; ?> grid_<?php echo $h->css->s_snet; ?>">
        <div class="addthis_toolbox addthis_default_style" id="snet">
         <a class="addthis_button_facebook"></a>
         <a class="addthis_button_twitter"></a>
         <a class="addthis_button_email"></a>
         <a class="addthis_button_print"></a>
         <a class="addthis_button_google_plusone"></a>
        </div>
       </div>
       <div class="clear clearfix"></div>
  <p><?php echo $title; ?></p>
  <div class="ctable">
   <table id="legend" class="ctable"><tr>
				<td class="greentd">RECOMMENDED</td>
				<td class="orangetd">SECURITY</td>
				<td class="redtd">WITHDRAWN</td>
				<td class="violettd">OBSOLETE</td>
			</tr></table>
 <?php if (isset($l)) { ?>
  <div id="add_uclist_form">
   <select id="selectAddUCList" name="i">
    <option value="-1" selected>Add to Custom List</option>
    <?php foreach($l->a_uclists as $l) { ?>
    <option value="<?php echo $l->id; ?>"><?php echo $l->name; ?></option>
    <?php } ?>
   </select>
   <input type="button" name="Add" value="Add" onclick="addManyUCList(showMessage)"/>
   <div id="msg_uclist"></div>
  </div>
 <?php } ?>
  <p class="paging"><?php echo $pagination; ?></p>
  <table id="tbl_patches" class="ctable">
   <tr>
    <?php if (isset($l)) { ?><th></th><?php } ?>
    <th>Patch ID</th>
    <th>Release date</th>
    <th>Synopsis</th>
   </tr>
<?php $i=0; foreach($patches as $p) { ?>
   <tr>
    <?php if (isset($l)) { ?><td><input type="checkbox" name="p[<?php echo $i; ?>]" value="<?php echo $p->name(); ?>"/></td><?php } ?>
    <td <?php echo $p->color(); ?>><?php if ($p->isNew()) { ?><img class="newimg" src="/img/new.png" alt="New"/> <?php } ?><a href="/patch/id/<?php echo $p->name(); ?>"><?php echo $p->name(); ?></a></td>
    <td><?php echo date(HTTP::getDateFormat(), $p->releasedate); ?></td>
    <td style="text-align: left"><?php echo HTTP::linkize(substr($p->synopsis,0, $h->css->s_strip)); ?></td>
   </tr>
<?php $i++; } ?>
   </table>
  <p class="paging"><?php echo $pagination; ?></p>
  </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 --> 
 </div><!-- d_content -->
