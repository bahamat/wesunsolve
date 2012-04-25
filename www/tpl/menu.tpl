<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
  <div class="container_24"> 
   <div class="grid_<?php echo $h->css->s_menu; ?>"> 
    <div id="d_menu"> 
     <ul> 
      <li class="m_header">Patches</li> 
      <li class="m_element"><a href="/bundles">Patches bundles</a></li> 
      <li class="m_element"><a href="/lpatches">Latest patches</a></li> 
      <li class="m_element"><a href="/lcve">Latest security alerts</a></li>
      <li class="m_element"><a href="/lcvep">Latest CVE patches</a></li>
      <li class="m_element"><a href="/lpatches/sec/1">Latest security patches</a></li> 
      <li class="m_element"><a href="/lpatches/bad/1">Latest obsoleted patches</a></li> 
      <li class="m_element"><a href="/lastreadme">Latest updated READMEs</a></li> 
      <li class="m_element"><a href="/patch_search">Search for patches</a></li> 
      <li class="m_element"><a href="/plist_report">Create patch reports</a></li> 
      <li class="m_element"><a href="/compare">Compare patch levels</a></li> 
      <li class="m_element"><a href="/patchdiag">Patchdiag archive</a></li> 
     </ul> 
     <ul> 
      <li class="m_header">Bugs</li> 
      <li class="m_element"><a href="/bsearch">Bug search</a></li> 
     </ul> 
     <ul>
      <li class="m_header">Next Gen</li>
      <li class="m_element"><a href="/ips">Latest S11 packages</a></li>
      <li class="m_element"><a href="/lips">List of repositories</a></li>
      <li class="m_element"><a href="/lbugfix">Latest fixed bugs</a></li>
      <li class="m_element"><a href="/pkgsearch">Search for packages</a></li>
     </ul>
     <ul>
      <li class="m_header">Fingerprint Database</li>
      <li class="m_element"><a href="/releases">Referenced releases</a></li>
      <li class="m_element"><a href="/fsearch">File search</a></li>
     </ul>
     <ul> 
      <li class="m_header">User</li> 
<?php $lm = loginCM::getInstance(); if (!isset($lm->o_login) || !$lm->o_login) { ?>
      <li class="m_element"><a href="https://wesunsolve.net/login">Login</a></li>
      <li class="m_element"><a href="/register">Register</a></li>
<?php } else { ?>
      <li class="m_element"><a href="/panel">Panel</a></li>
      <li class="m_element"><a href="/mlist">Mail reports</a></li>
      <li class="m_element"><a href="/add_clist">Add custom list</a></li>
      <li class="m_element"><a href="/register_srv">Add server</a></li>
      <li class="m_element"><a href="/password">Change password</a></li>
      <li class="m_element"><a href="/settings">Change settings</a></li>
      <li class="m_element"><a href="/logout">Logout</a></li>
<?php } ?>
     </ul> 
     <ul> 
      <li class="m_header">News</li> 
      <li class="m_element"><a href="http://wiki.wesunsolve.net/Backlinks">Backlinks</a></li> 
      <li class="m_element"><a href="http://wiki.wesunsolve.net/ThanksTo">Thanks to...</a></li> 
      <li class="m_element"><a href="http://wiki.wesunsolve.net/Documentation">Documentation</a></li> 
      <li class="m_element"><a href="http://wiki.wesunsolve.net">Wiki</a></li> 
      <li class="m_element"><a href="/stats">Figures and numbers</a></li> 
      <li class="m_element"><a href="/changelog">Change log</a></li> 
      <li class="m_element"><a href="/rss/bundles"><img src="/img/rss.png" alt="RSS Feed"/> RSS Latest bundles</a></li> 
      <li class="m_element"><a href="/rss/patches"><img src="/img/rss.png" alt="RSS Feed"/> RSS Latest patches</a></li> 
      <li class="m_element"><a href="/rss/s11pkg"><img src="/img/rss.png" alt="RSS Feed"/> RSS Latest packages</a></li> 
      <li class="m_element"><a href="/rss/lbugfix"><img src="/img/rss.png" alt="RSS Feed"/> RSS Latest bugs fixed</a></li> 
      <li class="m_element"><a href="/rss/news"><img src="/img/rss.png" alt="RSS Feed"/> RSS Site news</a></li> 
     </ul> 
     <ul> 
      <li class="m_header">Links</li> 
      <li class="m_element"><a href="http://support.oracle.com">My Oracle Support</a></li> 
      <li class="m_element"><a href="http://www.sun.com/bigadmin/home/index.jsp">Big Admin</a></li> 
      <li class="m_element"><a href="http://www.par.univie.ac.at/solaris/pca/">Patch check advanced</a></li> 
      <li class="m_element"><a href="http://www.solarisinternals.com">Solaris Internals</a></li> 
      <li class="m_element"><a href="http://www.sunfreeware.com">Sun Freeware</a></li> 
      <li class="m_element"><a href="http://filibeto.org">Filibeto</a></li> 
      <li class="m_element"><a href="http://www.opencsw.org">OpenCSW</a></li> 
      <li class="m_element"><a href="http://wildness.espix.org">wildcat's blog</a></li> 
      <li class="m_element"><a href="http://www.espix.org">Espix Network</a></li> 
     </ul> 
     <p>2.0</p> 
 
    </div><!-- d_menu --> 
   </div><!-- grid_<?php echo $h->css->s_menu; ?> --> 
 
   <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?>">
