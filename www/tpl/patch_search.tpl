<div class="content">
  <h2>Advanced patch search</h2>
  <form method="post" action="/psearch.php/form/1">
  <div class="ctable">
  <p>You can fill the fields that you want to search on...</p>
  <p>You can use % for wildcards</p>
  <table class="ctable">
    <tr><th>Patch ID:</th><td><input type="text" name="pid"/></td></tr>
    <tr><th>Revision:</th><td><input type="text" name="rev"/></td></tr>
    <tr><th>Synopsis:</th><td><input type="text" name="synopsis"/></td></tr>
    <tr><th>Status:</th><td><input type="text" name="status"/></td></tr>
    <tr><th>File:</th><td><input type="text" name="files"/></td></tr>
<!--
    <tr><th>*Keyword:</th><td><input type="text" name="keyword"/></td></tr>
    <tr><th>*Architecture:</th><td><input type="text" name="arch"/></td></tr>
    <tr><th>*Solaris release: </th><td><input type="text" name="sol_release"/></td></tr>
    <tr><th>*SunOS release:</th><td><input type="text" name="sun_release"/></td></tr>
    <tr><th>*Unbundled product:</th><td><input type="text" name="un_product"/></td></tr>
    <tr><th>*Unbundled release:</th><td><input type="text" name="un_release"/></td></tr>
    <tr><th>*Patches that obsolete:</th><td><input type="text" name="obso"/></td></tr>
    <tr><th>*Patches in conflicts with:</th><td><input type="text" name="conflicts"/></td></tr>
    <tr><th>*Patches that requires:</th><td><input type="text" name="requires"/></td></tr>
    <tr><td colspan="2">Fields marked of a * are currently in construction and thus NOT working</td></tr>
-->
    <tr><td></td><td><input type="submit" value="search"/></td></tr>
  </table>
  </div>
  </form>
</div>

