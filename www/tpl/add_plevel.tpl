<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>  
    <div id="d_content">
     <h2 class="grid_10 push_1 alpha omega">Add patch level</h2>
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
  <p>Add patch level for <?php echo $s->name; ?></p>
    <p>Paste below the list of patches using one of the available formats</p>
      <ul class="bullet">
       <li><b>PCA</b>: Output of <span class="code">pca -l m</span></li>
       <li><b>Text</b>: One full patch number per line (e.g.:123456-78)</li>
       <li><b>showrev</b>: Output of <span class="code">showrev -p</span></li>
      </ul>
    <br/>
    <p>You could also use a file to upload directly, but you must fill one of the field...</p>
<?php if (isset($error)) { ?>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
    <form enctype="multipart/form-data" method="POST" action="/add_plevel/form/1/s/<?php echo $s->id; ?>">
    <table class="ctable">
      <tr><td>*Server Name</td><td><?php echo $s->name; ?></td></tr>
      <tr><td>Name of patch level</td><td><input type="text" value="<?php if (isset($name)) echo $name; ?>" name="name"></td></tr>
      <tr><td>Comment</td><td><input type="text" value="<?php if (isset($comment)) echo $comment; ?>" name="comment"></td></tr>
      <tr><td>Current ?</td><td><input type="checkbox" <?php if (isset($current) && $current) echo "checked"; ?> name="is_current"></td></tr>
      <tr><td>Applied ?</td><td><input type="checkbox" <?php if (isset($applied) && $applied) echo "checked"; ?> name="is_applied"></td></tr>
      <tr><td>Format</td>
        <td><select name="format">
              <option value="pca">PCA</option>
              <option value="text">Text</option>
              <option value="showrev">Showrev</option>
            </select></td>
      </tr>
      <tr><td>File source</td>
	  <td><input type="file" name="plist_file"/></td>
      </tr>
      <tr><td>Patch list</td>
       <td><textarea rows="50" cols="50" name="plist"></textarea></td>
      </tr>
      <tr><td></td><td><input type="submit" value="Register" name="save"></td></tr>
    </table>
    </form>
   </div><!-- d_content_box -->
  </div><!-- grid_19 -->
 </div><!-- d_content -->
