<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="">

		<title>Disposable Email Lists<?= (!empty($page['title']) ? ' - '.$page['title'] : '') ?></title>

		<!-- Fonsts and icons -->
		<link href="https://fonts.googleapis.com/css?family=Roboto:300,300i,400,400i,500,500i,700,700i" rel="stylesheet">
		<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" integrity="sha256-eZrrJcwDc/3uDhsdt61sL2oOBY362qM3lon1gyExkL0=" crossorigin="anonymous" />
		<!-- Bootstrap core CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
        <!-- Material CSS -->
        <link href="/css/material.css" rel="stylesheet">
        
		<!-- Custom styles for this template -->
		<link href="/css/style.css" rel="stylesheet">
		<?= $f3->decode($css) ?>
	</head>

	<body>
		<div class="container">
			<div class="header clearfix">
				<nav>
					<ul class="nav nav-pills float-right">
						<li class="nav-item">
							<a class="nav-link<?= ($PATH == '/' ? ' active' : null) ?>" href="/">Home <span class="sr-only">(current)</span></a>
						</li>
						<li class="nav-item">
							<a class="nav-link<?= ($PATH == '/submit' ? ' active' : null) ?>" href="/submit">Submit</a>
						</li>
						<li class="nav-item">
							<a class="nav-link<?= ($PATH == '/api' ? ' active' : null) ?>" href="/api">API</a>
						</li>					
						<li class="nav-item">
							<a class="nav-link<?= ($PATH == '/stats' ? ' active' : null) ?>" href="/stats">Stats</a>
						</li>
					</ul>
				</nav>
				<h3 class="text-muted"><img src="/img/logo.png"> Disposable Email Lists</h3>
			</div>

			<?= $f3->decode($page['body']) ?>
            <br>
			<footer class="footer">
				<p>An <a href="https://github.com/lcherone/diseml" target="_blank">Open Source Project</a> By Lawrence Cherone.</p>
			</footer>

		</div>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
        
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js" integrity="sha384-b/U6ypiBEHpOf/4+1nzFpr53nxSS+GLCkfwBdFNTxtclqqenISfwAzpKaMNFNmj4" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js" integrity="sha384-h0AbiXch4ZDo7tp9hKZ4TsHbi047NrKGLO3SEJAg45jXxnGIfYzk4Si90RDIqNm1" crossorigin="anonymous"></script>
		<script src="/js/material.js"></script>
		<?= $f3->decode($javascript) ?>
	</body>
</html>
<?php return; ?>



<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="<?= $meta['description'] ?>">
		<meta name="author" content="<?= $meta['author'] ?>">

		<title><?= $setting['sitename'] ?><?= (!empty($page['title']) ? ' - '.$page['title'] : '') ?></title>

		<!-- bootstrap core css -->
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/css/bootstrap.css" integrity="sha256-fmMNkMcjSw3xcp9iuPnku/ryk9kaWgrEbfJfKmdZ45o=" crossorigin="anonymous" />

		<!-- custom styles for this template -->
		<link href="/css/styles.css" rel="stylesheet">

		<?= $f3->decode($css) ?>
	</head>
	<body>

		<div id="wrapper">
			<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
				<div class="navbar-header">
					<?php if (!empty($_SESSION['user'])): ?>
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<?php endif ?>
					<a class="navbar-brand" href="/" class="ajax-link"><?= $setting['sitename'] ?></a>
				</div>
				<div class="collapse navbar-collapse navbar-collapse">
					<ul class="nav navbar-nav side-nav">
						<?php foreach ($menus as $row): ?>
						<?php 
						// check for admin only
						if (empty($f3->get('SESSION.user')) && $row->visibility == 4) {
							continue;
						}
						?>
						<li<?= ($PATH == $row->slug ? ' class="active"' : '') ?>><a href="<?= $row->slug ?>"><?= (!empty($row->icon) ? '<i class="'.$row->icon.'"></i> ' : '') ?><?= $row->title ?></a></li>
						<?php endforeach ?>
						<?php if (!empty($_SESSION['user'])): ?>
						<li<?= ($PATH == '/admin' ? ' class="active"' : '') ?>><a href="/admin"><i class="fa fa-user-secret"></i> Developer</a></li>
						<?php endif ?>
					</ul>
				</div>
			</nav>
			<div id="page-wrapper">
				<div class="container-fluid ajax-container">
					<?= $f3->decode($page['body']) ?>
				</div>
			</div>
		</div>

		<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.12.4/jquery.min.js" integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ=" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha256-U5ZEeKfGNOja007MMD3YBI0A3OSZOQbeG6z2f2Y0hu8=" crossorigin="anonymous"></script>
		<script src="/js/app.js"></script>
		<?= $f3->decode($javascript) ?>
	</body>
</html>