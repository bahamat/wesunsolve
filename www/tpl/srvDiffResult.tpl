<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Comparison of two servers - report</h2>
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
  <div class="ctable">
  <table class="ctable">
    <tr>
      <th colspan="2"><?php echo $ps->o_server->name." - ".$ps->name; ?></th>
      <th colspan="2"><?php echo $pd->o_server->name." - ".$pd->name; ?></th>
      <th colspan="2">Latest</th>
    </tr>
    <tr>
      <th>Patch</th>
      <th>Released</th>
      <th>Patch</th>
      <th>Released</th>
      <th>Patch</th>
      <th>Released</th>
    </tr>
<?php foreach($delta as $k => $p) { ?>
    <tr>
      <td><?php if (isset($p[1]) && $p[1]) { echo "<a href=\"/patch/id/".$p[1]->name()."\">".$p[1]->name()."</a>"; } else { echo "Missing"; } ?></td>
      <td><?php if (isset($p[1]) && $p[1]) { if($p[1]->releasedate) echo date(HTTP::getDateFormat(), $p[1]->releasedate); } else { echo "&nbsp;"; } ?></td>
      <?php if (isset($p[2]) && $p[2] && isset($p[1]) && $p[1] && $p[1]->revision == $p[2]->revision) { ?>
	<td>same</td><td>&nbsp;</td>
      <?php } else { ?>
      <td><?php if (isset($p[2]) && $p[2]) { echo "<a href=\"/patch/id/".$p[2]->name()."\">".$p[2]->name()."</a>"; } else { echo "Missing"; } ?></td>
      <td><?php if (isset($p[2]) && $p[2]) { if($p[2]->releasedate) echo date(HTTP::getDateFormat(), $p[2]->releasedate); } else { echo "&nbsp;"; } ?></td>
      <?php } ?>
      <td><?php if (isset($p['latest']) && $p['latest']) { echo "<a href=\"/patch/id/".$p['latest']->name()."\">".$p['latest']->name()."</a>"; } else { echo "Missing"; } ?></td>
      <td><?php if (isset($p['latest']) && $p['latest']) { if($p['latest']->releasedate) echo date(HTTP::getDateFormat(), $p['latest']->releasedate); } else { echo "&nbsp;"; } ?></td>
    </tr>
<?php } ?>
  </table>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
</div>
