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
      <li class="m_element"><a href="/lpatches/sec/1">Latest security patches</a></li> 
      <li class="m_element"><a href="/lpatches/bad/1">Latest obsoleted patches</a></li> 
      <li class="m_element"><a href="/patch_search">Search for patches</a></li> 
      <li class="m_element"><a href="/plist_report">Patches report</a></li> 
      <li class="m_element"><a href="/compare">Patch compare</a></li> 
      <li class="m_element"><a href="/patchdiag">Patchdiag archive</a></li> 
     </ul> 
     <ul> 
      <li class="m_header">Bugs</li> 
      <li class="m_element"><a href="/bsearch">Bug search</a></li> 
     </ul> 
     <ul> 
      <li class="m_header">User</li> 
<?php $lm = loginCM::getInstance(); if (!isset($lm->o_login) || !$lm->o_login) { ?>
      <li class="m_element"><a href="/login">Login</a></li>
      <li class="m_element"><a href="/register">Register</a></li>
<?php } else { ?>
      <li class="m_element"><a href="/panel">Panel</a></li>
      <li class="m_element"><a href="/add_clist">Add custom list</a></li>
      <li class="m_element"><a href="/register_srv">Add server</a></li>
      <li class="m_element"><a href="/password">Change password</a></li>
      <li class="m_element"><a href="/settings">Change settings</a></li>
      <li class="m_element"><a href="/logout">Logout</a></li>
<?php } ?>
     </ul> 
     <ul> 
      <li class="m_header">Various</li> 
      <li class="m_element"><a href="/dbstats">Database statistics</a></li> 
      <li class="m_element"><a href="/help">Help us</a></li> 
      <li class="m_element"><a href="/changelog">Change log</a></li> 
      <li class="m_element"><a href="/docs">Documentation</a></li> 
      <li class="m_element"><a href="/rss/bundles"><img src="/img/rss.png" alt="RSS Feed"/> RSS Last bundles</a></li> 
      <li class="m_element"><a href="/rss/patches"><img src="/img/rss.png" alt="RSS Feed"/> RSS Last patches</a></li> 
      <li class="m_element"><a href="/rss/news"><img src="/img/rss.png" alt="RSS Feed"/> RSS Site news</a></li> 
     </ul> 
     <ul> 
      <li class="m_header">Links</li> 
      <li class="m_element"><a href="http://support.oracle.com">My Oracle Support</a></li> 
      <li class="m_element"><a href="http://www.sun.com/bigadmin/home/index.jsp">Big Admin</a></li> 
      <li class="m_element"><a href="http://www.par.univie.ac.at/solaris/pca/">Patch check advanced</a></li> 
      <li class="m_element"><a href="http://www.solarisinternals.com">Solaris internal</a></li> 
      <li class="m_element"><a href="http://www.sunfreeware.com">Sun Freewares</a></li> 
      <li class="m_element"><a href="http://www.opencsw.org">OpenCSW</a></li> 
      <li class="m_element"><a href="http://wildness.espix.org">wildcat's blog</a></li> 
      <li class="m_element"><a href="http://www.espix.org">Espix Network</a></li> 
     </ul> 
     <p>2.0</p> 
 
    </div><!-- d_menu --> 
   </div><!-- grid_<?php echo $h->css->s_menu; ?> --> 
 
   <div class="grid_<?php echo ($h->css->s_total - $h->css->s_menu); ?>">
