<!DOCTYPE html> 
<html> 
	<head> 
	<?php if (isset($title) && !empty($title)) { ?>
	<title><?php echo $title; ?></title> 
	<?php } else { ?>
	<title>We Sun Solve! - A bunch of information about Solaris</title> 
	<?php } ?>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta http-equiv="Content-Language" content="fr" /> 
	<link rel="stylesheet" href="/css/jquery.mobile-1.0b1.min.css" /> 
	<script src="/js/jquery-1.6.2.min.js"></script> 
	<script src="/js/jquery.mobile-1.0b1.min.js"></script> 
	<link rel="stylesheet" type="text/css" href="/css/main.css" media="screen" />
</head> 
<body> 
<div data-role="page" data-theme="b"> 
 
	<div data-role="header" data-nobackbtn="true"> 
		<a href="/" data-icon="home">Home</a>
		<h1>We Sun Solve! / <?php echo $paget; ?></h1> 
	</div>
