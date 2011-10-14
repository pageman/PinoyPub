<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset=utf-8>
		<title><?= $title ?></title>
		<link rel="stylesheet" href="/assets/templates/52framework/css/reset.css" type="text/css"  />
		<link rel="stylesheet" href="/assets/templates/52framework/css/grid.css" type="text/css" />
		<link rel="stylesheet" href="/assets/templates/52framework/css/general.css" type="text/css" />
		<?= $css_string ?>
		<script src="/assets/templates/52framework/js/modernizr-1.7.min.js"></script><!-- this is the javascript allowing html5 to run in older browsers -->
		<?= $javascript ?>
	</head>

	<body>
		<div class="row logo">
			<div class="col col_12">
				<h1><?= $this->config->item('template_title_default') ?></h1>
			</div>
			<div class="col col_4 align_right">
				<a href="/">Home</a> | <a href="/users/register">Register</a> | <a href="/users/login">Login</a>
			</div>
		</div>
		<div class="row">
			<div class="col col_16 breadcrumbs">
				<?= $bc ?>
			</div>
		</div>