   <div class="content">
    <h2>Add patch level for <?php echo $s->name; ?></h2>
    <p>Copy/Paste below the list in the specified format...</p>
      <ul>
       <li><b>PCA</b>: Output of pca -l m</li>
       <li><b>Text</b>: One patch per line</li>
       <li><b>showrev</b>: Output of showrev -p</li>
      </ul>
    <p>You could also use a file to upload directly, but you must fill one of the field...</p>
<?php if (isset($error)) { ?>
    <span class="red"><p><?php echo $error; ?></p></span>
<?php } ?>
    <form enctype="multipart/form-data" method="POST" action="/add_plevel.php/form/1/s/<?php echo $s->id; ?>">
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
    <hr/>
   </div>
