<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Admin panel</h2>
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
<?php if (isset($error)) { ?>
    <br/>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>

    <h4>Last users seen</h4>
    <div class="ctable">
     <table class="ctable">
     <tr>
       <th>Last seen</th>
       <th>Login</th>
     </tr>
<?php foreach ($llogins as $l) { ?>
     <tr>
       <td><?php echo date(HTTP::getDateTimeFormat(), $l->last_seen); ?></td>
       <td><?php echo $l->username; ?></td>
     </tr>
<?php } ?>
   </table>
   </div>

    <h4>Last 20 Failed login (Total: <?php echo MysqlCM::getInstance()->count("login_failed"); ?>)</h4>
    <div class="ctable">
     <table class="ctable">
     <tr>
       <th>When</th>
       <th>IP</th>
       <th>Login</th>
       <th>Agent</th>
     </tr>
<?php foreach ($flogins as $f) { ?>
     <tr>
       <td><?php echo date(HTTP::getDateTimeFormat(), $f->when); ?></td>
       <td><?php echo $f->ip; ?></td>
       <td><?php echo $f->login; ?></td>
       <td><?php echo substr($f->agent, 0, 20); ?></td>
     </tr>
<?php } ?>
   </table>
   </div>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
