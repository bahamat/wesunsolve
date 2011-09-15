<?php
  $h = HTTP::getInstance();
  if (!$h->css) $h->fetchCSS();
?>
<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
 <head> 
<?php if (isset($title) && !empty($title)) { ?>
  <title><?php echo $title; ?></title>
<?php } else { ?>
  <title>We Sun Solve ! - A heap of information about Solaris</title>
<?php } ?>
  <link rel="stylesheet" type="text/css" href="/css/reset2.css" /> 
  <link rel="stylesheet" type="text/css" href="/css/<?php echo $h->css->css_file; ?>" /> 
  <link rel="stylesheet" type="text/css" href="/css/wesunsolve.css" /> 
  <script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js"></script> 
<?php if (isset($head_add)) echo $head_add; ?>
 </head> 
 <body<?php if (isset($bargs) && !empty($bargs)) { echo " $bargs"; } ?>> 
 
  <div class="container_24"> 
   <div id="header" class="grid_24 d_bar"> 
 
    <div id="d_title" class="grid_20 alpha"> 
      <h1><a href="/">We Sun Solve!</a></h1> 
      <h2>A heap of information about the Solaris operating system...</h2> 
    </div> 
 
    <div id="d_search" class="grid_4 omega"> 
      <form id="f_menu_patch" method="post" action="/psearch"> 
       <fieldset style="min-width: 100px"> 
         <input type="text" id="f_menu_patch_input" name="pid" value="" placeholder="Patch ID..." /><input type="image" src="/img/zoom.png" alt="Patch search"/> 
       </fieldset> 
     </form> 
     <form id="f_menu_bug" method="post" action="/bsearch"> 
       <fieldset style="min-width: 100px"> 
        <input type="text" id="f_menu_bug_input" name="bid" value="" placeholder="Bug ID..." /><input type="image" src="/img/zoom.png" alt="Bug search"/> 
       </fieldset> 
     </form> 
    </div> 
 
   </div><!-- header --> 
 
  </div><!-- container_24 --> 

