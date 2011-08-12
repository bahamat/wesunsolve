<?php if (isset($p)) { ?>
<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Compare 2 Server - report</h2>
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
      <th colspan="2">Server 1</th>
      <th colspan="2">Server 2</th>
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
      <td><?php if (isset($p[2]) && $p[2]) { echo "<a href=\"/patch/id/".$p[2]->name()."\">".$p[2]->name()."</a>"; } else { echo "Missing"; } ?></td>
      <td><?php if (isset($p[2]) && $p[2]) { if($p[2]->releasedate) echo date(HTTP::getDateFormat(), $p[2]->releasedate); } else { echo "&nbsp;"; } ?></td>
      <td><?php if (isset($p['latest']) && $p['latest']) { echo "<a href=\"/patch/id/".$p['latest']->name()."\">".$p['latest']->name()."</a>"; } else { echo "Missing"; } ?></td>
      <td><?php if (isset($p['latest']) && $p['latest']) { if($p['latest']->releasedate) echo date(HTTP::getDateFormat(), $p['latest']->releasedate); } else { echo "&nbsp;"; } ?></td>
    </tr>
<?php } ?>
  </table>
  </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
<?php
 } else { ?>
<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Compare 2 Server - report</h2>
     <div class="clear"></div>
     <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?> alpha omega">
      <div class="d_content_box">
      <div class="push_<?php echo $h->css->p_snet; ?> grid_<?php echo $h->css->s_snet; ?>">
        <div class="addthis_toolbox addthis_default_style" id="snet">
         <a class="addthis_button_facebook"></a>
         <a class="addthis_button_twitter"></a>
         <a class="addthis_button_email"></a>
         <a class="addthis_button_print"></a>
         <a class="addthis_button_google_plusone"></a>
        </div>
       </div>
  <form method="post" action="/compare/form/1">
  <div class="ctable">
  <p>Copy/Paste below the list in the specified format...</p>
  <ul>
   <li><b>PCA</b>: Output of pca -l m</li>
   <li><b>Text</b>: One patch per line</li>
   <li><b>showrev</b>: Output of showrev -p</li>
  </ul>
  <table class="ctable">
    <tr><th>Server 1</th><th>Server 2</th></tr>
    <tr>
        <td><select name="format1">
	      <option value="pca">PCA</option>
	      <option value="text">Text</option>
	      <option value="showrev">Showrev</option>
            </select></td>
        <td><select name="format2">
	      <option value="pca">PCA</option>
	      <option value="text">Text</option>
	      <option value="showrev">Showrev</option>
            </select></td>
    </tr>
    <tr>
       <td><textarea rows="50" cols="50" name="plist1"></textarea></td>
       <td><textarea rows="50" cols="50" name="plist2"></textarea></td>
    </tr>
    <tr><td colspan="2"><input type="submit" value="search"/></td></tr>
  </table>
  </div>
  </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
<?php } ?>

