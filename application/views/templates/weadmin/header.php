<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd"> 
<html> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> 
 
<!-- Website Title --> 
<title>WeAdmin | Dashboard</title>

<!-- Meta data for SEO -->
<meta name="description" content="">
<meta name="keywords" content="">

<!-- Template stylesheet -->
<link href="/assets/templates/weadmin/css/blue/screen.css" rel="stylesheet" type="text/css" media="all">
<link href="/assets/templates/weadmin/css/blue/datepicker.css" rel="stylesheet" type="text/css" media="all">
<link href="/assets/templates/weadmin/css/tipsy.css" rel="stylesheet" type="text/css" media="all">
<link href="/assets/templates/weadmin/js/visualize/visualize.css" rel="stylesheet" type="text/css" media="all">
<link href="/assets/templates/weadmin/js/jwysiwyg/jquery.wysiwyg.css" rel="stylesheet" type="text/css" media="all">
<link href="/assets/templates/weadmin/js/fancybox/jquery.fancybox-1.3.0.css" rel="stylesheet" type="text/css" media="all">
<link href="/assets/templates/weadmin/css/tipsy.css" rel="stylesheet" type="text/css" media="all">

<!--[if IE]>
	<link href="/assets/templates/weadmin/css/ie.css" rel="stylesheet" type="text/css" media="all">
	<script type="text/javascript" src="/assets/templates/weadmin/js/excanvas.js"></script>
<![endif]-->

<!-- Jquery and plugins -->
<script type="text/javascript" src="/assets/templates/weadmin/js/jquery.js"></script>
<script type="text/javascript" src="/assets/templates/weadmin/js/jquery-ui.js"></script>
<script type="text/javascript" src="/assets/templates/weadmin/js/jquery.img.preload.js"></script>
<script type="text/javascript" src="/assets/templates/weadmin/js/hint.js"></script>
<script type="text/javascript" src="/assets/templates/weadmin/js/visualize/jquery.visualize.js"></script>
<script type="text/javascript" src="/assets/templates/weadmin/js/jwysiwyg/jquery.wysiwyg.js"></script>
<script type="text/javascript" src="/assets/templates/weadmin/js/fancybox/jquery.fancybox-1.3.0.js"></script>
<script type="text/javascript" src="/assets/templates/weadmin/js/jquery.tipsy.js"></script>
<script type="text/javascript" src="/assets/templates/weadmin/js/custom_blue.js"></script>

</head>
<body>
<div class="content_wrapper">

	<!-- Begin header -->
	<div id="header">
		<div id="logo">
			<img src="/assets/templates/weadmin/images/logo.png" alt="logo"/>
		</div>
		<div id="search">
			<form action="dashboard.html" id="search_form" name="search_form" method="get">
				<input type="text" id="q" name="q" title="Search" class="search noshadow"/>
			</form>
		</div>
		<div id="account_info">
			<img src="/assets/templates/weadmin/images/icon_online.png" alt="Online" class="mid_align"/>
			Hello <a href="">Admin</a> (<a href="">1 new message</a>) | <a href="">Setting</a> | <a href="/admin/logout/">Logout</a>
		</div>
	</div>
	<!-- End header -->
	
	
	<!-- Begin left panel -->
	<a href="javascript:;" id="show_menu">&raquo;</a>
	<div id="left_menu">
		<a href="javascript:;" id="hide_menu">&laquo;</a>
		<ul id="main_menu">
			<li><a href="/admin/"><img src="/assets/templates/weadmin/images/icon_home.png" alt="Home"/>Home</a></li>
			<li>
				<a href=""><img src="/assets/templates/weadmin/images/icon_users.png" alt="Users"/>Users</a>
				<ul>
					<li><a href="/admin/auth_user_add/">Add User</a></li>
					<li><a href="/admin/auth_user/">View Users</a></li>
				</ul>	
			</li>
			<li>
				<a href=""><img src="/assets/templates/weadmin/images/icon_groups.png" alt="Groups"/>Groups</a>
				<ul>
					<li><a href="/admin/auth_group_add/">Add Group</a></li>
					<li><a href="/admin/auth_group/">View Groups</a></li>
				</ul>	
			</li>
		</ul>
		<br class="clear"/>
		
		<!-- Begin left panel calendar -->
		<div id="calendar"></div>
		<!-- End left panel calendar -->
		
	</div>
	<!-- End left panel -->
	<!-- Begin content -->
	<div id="content">
		<div class="inner">