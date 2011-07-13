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
     <h3>/links+friends</h3>
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
    <form action="https://www.paypal.com/cgi-bin/webscr" method="post">
      <fieldset style="border: 0px; text-align:center"><input type="hidden" name="cmd" value="_s-xclick"/>
      <input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
"/>
      <input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="PayPal - The safer, easier way to pay online!"/>
      <img alt="" style="border: 0px" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1"/>
      </fieldset>
    </form>
  <p style="text-align: center;">
    <a href="http://validator.w3.org/check?uri=referer"><img
        src="http://www.w3.org/Icons/valid-xhtml10"
        alt="Valid XHTML 1.0 Strict" height="31" width="88" style="border: 0px;" /></a>
  </p>
   </div>
