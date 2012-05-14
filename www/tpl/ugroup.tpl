<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">User group <?php echo $ugroup->name; ?></h2>
     <div class="clear"></div>
     <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?> alpha omega">
      <div class="d_content_box">
       <div style="height: 30px" class="push_<?php echo $h->css->p_snet; ?> grid_<?php echo $h->css->s_snet; ?> alpha omega">
        <div class="addthis_toolbox addthis_default_style" id="snet">
         <a class="addthis_button_facebook"></a>
         <a class="addthis_button_twitter"></a>
         <a class="addthis_button_email"></a>
         <a class="addthis_button_print"></a>
         <a class="addthis_button_google_plusone"></a>
        </div>
       </div>
       <div class="clear clearfix"></div>
    <p class="red"><?php if (isset($error) && !empty($error)) echo $error; ?></p>
    <?php if (isset($msg)) { ?><p><?php echo $msg; ?></p><?php } ?>
    <p>There are <?php echo count($ugroup->a_users); ?> user(s) in this group</p>
    <p>There are <?php echo count($ugroup->a_srv); ?> server(s) in this group</p>
  <table class="ctable">
    <tr>
      <th>Username</th>
      <th></th>
    </tr>
<?php foreach($ugroup->a_users as $p) { ?>
    <tr>
      <td><?php echo $p; ?></td>
      <td><a href="/ugroup/id/<?php echo $ugroup->id; ?>/del/<?php echo $p->id; ?>">remove</a></td>
      <td></td>
    </tr>
<?php } ?>
   </table>
   <h4>Add one user to the group</h4>
   <p>To add a user of WeSunSolve, you must first know its username, then, fill the form below:</p>
   <div class="ctable">
   <form method="POST" action="/ugroup/id/<?php echo $ugroup->id; ?>/form/1">
    <p>
    Username to add: <input type="text" name="uname"/>
    <input type="submit" name="Add" value="Add"/>
    </p>
   </form>
   </div>
   <h4>Mass-Add users to a group</h4>
   <p>In case you manage a team of people, you can add the whole team at once using this text area, enter one username per line!</p>
   <form method="POST" action="/ugroup/id/<?php echo $ugroup->id; ?>/form/1">
   <table class="ctable">
     <tr>
      <th style="vertical-align: top">Usernames to be added</th><td><textarea name="unames"></textarea></td>
     </tr>
     <tr><th></th><td><input type="submit" name="Add" value="Add"/></td></tr>
   </table>
   </form>
   <h4>Servers access for the group</h4>
   <p>You can here manage which server are available to the above group.</p>
   <table class="ctable">
    <tr>
      <th>Server name</th>
      <th>Write?</th>
      <th></th>
    </tr>
<?php foreach($ugroup->a_srv as $p) { ?>
    <tr>
      <td><?php echo $p; ?></td>
      <td><?php echo HTTP::eval_img($p->w); ?></td>
      <td><a href="/ugroup/id/<?php echo $ugroup->id; ?>/dels/<?php echo $p->id; ?>">remove</a></td>
      <td></td>
    </tr>
<?php } ?>
   </table>
   <h4>Add server access to the group</h4>
   <form method="POST" action="/ugroup/id/<?php echo $ugroup->id; ?>/form/1">
   <select name="sname">
    <option value="" selected>--- Select a server to be added</option>
<?php foreach($l->a_servers as $p) { ?>
    <option value="<?php echo $p->id; ?>"><?php echo $p; ?></option>
<?php } ?>
   </select>
   <input type="checkbox" name="rw" value="1"/> Write access
    <input type="submit" name="Add" value="Add"/>
   </form>
   <h4>Mass-Add server access to the group</h4>
   <form method="POST" action="/ugroup/id/<?php echo $ugroup->id; ?>/form/1">
   <textarea name="snames"></textarea>
   <input type="checkbox" name="rw" value="1"/> Write access
    <input type="submit" name="Add" value="Add"/>
   </form>

   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
