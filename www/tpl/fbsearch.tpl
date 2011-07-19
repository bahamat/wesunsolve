<div class="content">
  <h2>Advanced bug search</h2>
  <form method="post" action="/bsearch.php/form/1">
  <div class="ctable">
  <p>You can fill the fields that you want to search on...</p>
  <table class="ctable">
    <tr><th>Bug ID:</th><td><input type="text" name="bid"/></td></tr>
    <tr><th>Full Text Search:</th><td><input type="text" name="synopsis"/></td></tr>
    <tr><th>Last activity:</th><td><select name="df">
				     <option selected value="">None</option>
				     <option value="1d">Last day</option>
				     <option value="1w">Last week</option>
				     <option value="2w">Last 2 weeks</option>
				     <option value="1m">Last month</option>
				     <option value="2m">Last 2 months</option>
				     <option value="6m">Last 6 months</option>
				     <option value="1y">Last year</option>
        		      </td></tr>
    <tr><td></td><td><input type="submit" value="search"/></td></tr>
  </table>
  </div>
  </form>
</div>

