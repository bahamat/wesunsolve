<div class="content">
  <h2>Patch List report</h2>
  <form method="post" action="/plist_report.php/form/1">
  <div class="ctable">
  <p>Copy/Paste below the list in the specified format...</p>
  <ul>
   <li><b>PCA</b>: Output of pca -l m</li>
   <li><b>Text</b>: One patch per line</li>
   <li><b>showrev</b>: Output of showrev -p</li>
  </ul>
  <table class="ctable">
    <tr><td><select name="format">
	      <option value="pca">PCA</option>
	      <option value="text">Text</option>
	      <option value="showrev">Showrev</option>
            </select></td></tr>
    <tr><td><textarea rows="40" cols="10" name="plist"></textarea></td></tr>
    <tr><td><input type="submit" value="search"/></td></tr>
  </table>
  </div>
  </form>
</div>
