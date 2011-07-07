   <div class="nav">
<p style="text-align:center;">
<?php
  $ip = getenv ("REMOTE_ADDR");
  if (substr_count($ip,":") > 0
      && substr_count($ip,".") == 0) {
    echo "<b>[Connected using IPv6]</b>";
  } else {
    echo "[Connected using IPv4]";
  }
?>
</p>
<?php
  $lm = loginCM::getInstance();
  if (isset($lm->o_login) && $lm->o_login) {
    echo "<p>Welcome ".$lm->o_login->fullname."</p>";
  } else {
    echo "<p>Not signed in</p>";
  }
?>
    <h2><a href="/">Home</a></h2>
     <h3>/infos</h3>
      <p>
        <a href="/bundles">Patch clusters</a><br/>
        <a href="/lpatches">Last Patches</a><br/>
        <a href="/lpatches/sec/1">Last Security Patches</a><br/>
        <a href="/lpatches/bad/1">Last Invalidated Patches</a><br/>
        <a href="/patch_search">Patches search</a><br/>
        <a href="/plist_report">Patches report</a><br/>
        <a href="/compare">Patches compare</a><br/>
        <a href="/bsearch">Bug search</a><br/>
      </p>
     <h3>/user</h3>
     <p>
<?php if (!isset($lm->o_login) || !$lm->o_login) { ?>
       <a href="/login">Login</a><br/>
       <a href="/register">Register</a><br/>
<?php } else { ?>
       <a href="/panel">Panel</a><br/>
       <a href="/register_srv">Add server</a><br/>
       <br/><a href="/logout">Logout</a><br/>
       <a href="/password">Change password</a><br/>
       <a href="/settings">Change settings</a><br/>
<?php } ?>
     </p>
     <h3>/various</h3>
      <p>
        <a href="/rss/bundles">RSS Last bundles</a><br/>
        <a href="/rss/patches">RSS Last patches</a><br/>
        <a href="/rss/news">RSS Site news</a><br/>
        <a href="/help">Help us !</a><br/>
        <a href="/changelog">Changelog</a><br/>
      </p>
     <h3>/search/patch</h3>
     <p>Use '%' as wildcard</p>
     <p>Only enter Patch-ID and Bug-ID</p>
     <form method="post" action="/psearch">
     <p>
      <input type="text" size="10" name="pid" value=""/>
      <input type="submit" value="search"/>
     </p>
     </form>
     <h3>/search/bugid</h3>
     <form method="post" action="/bsearch">
     <p>
      <input type="text" size="10" name="bid" value=""/>
      <input type="submit" value="search"/>
     </p>
     </form>
     <p>For deeper search, use the dedicated search pages</p>
     <h3>/links</h3>
      <p>
	<a href="http://support.oracle.com">My Oracle Support</a><br/>
	<a href="http://www.sun.com/bigadmin/home/index.jsp">Big Admin</a><br/>
	<a href="http://www.solarisinternals.com">Solaris Internal</a><br/>
	<a href="http://www.sunfreeware.com/">Sun Freewares</a><br/>
	<a href="http://www.opencsw.org/">OpenCSW</a><br/>

      </p>
      <p>
	<a href="http://wildness.espix.org">wildcat's blog</a><br/>
	<a href="http://www.espix.org">Espix Network</a><br/>
	<a href="/notify">Contact us</a><br/>
      </p>
  <br/>
  <p style="text-align: center;">
    <a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10"
        alt="Valid XHTML 1.0 Strict" height="31" width="88" style="border: 0px;" /></a>
  </p>
   </div>
