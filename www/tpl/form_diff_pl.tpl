<div class="content">
  <h2>Compare 2 Server - report</h2>
  <form method="post" action="/compare.php/form/1">
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
</div>
