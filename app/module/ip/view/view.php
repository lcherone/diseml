<?php ob_start() ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/2.8.0/css/flag-icon.min.css" integrity="sha256-EQjZwW4ljrt9dsonbyX+si6kbxgkVde47Ty9FQehnUg=" crossorigin="anonymous" />
<?php $f3->set('css', $f3->get('css').ob_get_clean()) ?>

<div class="card card-nav-tabs" id="port-forwards">
	<div class="card-header" data-background-color="blue">
		<h4 class="title">
			<i class="fa fa-globe"></i> IP: <?= $f3->get('PARAMS.action') ?>
			<a href="/ip/<?= $f3->get('PARAMS.action') ?>/json" class="pull-right mr-3 btn btn-primary">JSON</a>
			<a href="/ip/<?= $f3->get('PARAMS.action') ?>/txt" class="pull-right mr-3 btn btn-primary">TXT</a>
		</h4>
		<p class="category">All domains which point to this IP.</p>
	</div>
	<div class="card-content">
		<table class="table">
			<thead>
				<tr>
					<th>Domain</th>
					<th>IP</th>
					<th>ISP</th>
					<th>Country</th>
					<th>DNS</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php 
				    $i=1; foreach ($result as $row):
				?>
				<tr>
					<td><a href="/domain/<?= urlencode($row['domain']) ?>" class="ajax-link"><?= htmlentities($row['domain']) ?></a></td>
					<td><?= (!empty($row['ip']) ? '<a href="/ip/'.urlencode($row['ip']).'" class="ajax-link">'.htmlentities($row['ip']).'</a>' : null) ?></td>
					<td><?= (!empty($row['ipinfo']['isp']) ? '<a href="/isp/'.urlencode($row['ipinfo']['isp']).'" class="ajax-link">'.htmlentities(ucwords($row['ipinfo']['isp'])).'</a>' : null) ?></td>
					<td>
						<?= (!empty($row['geoinfo']['country_code']) ? '<i class="flag-icon flag-icon-'.htmlentities(strtolower($row['geoinfo']['country_code'])).'"></i> ' : null) ?>
						<?= (!empty($row['geoinfo']['country_name']) ? '<a href="/country/'.urlencode($row['geoinfo']['country_name']).'" class="ajax-link">'.htmlentities($row['geoinfo']['country_name']).'</a>' : null) ?>
					</td>
					<td>
					    <?= (!empty($row['dns']['A']) ? '<span class="badge badge-info">A</span> ' : '') ?>
					    <?= (!empty($row['dns']['MX']) ? '<span class="badge badge-success">MX</span> ' : '') ?>
					    <?= (!empty($row['dns']['CNAME']) ? '<span class="badge badge-info">CNAME</span> ' : '') ?>
					    <?= (!empty($row['dns']['NS']) ? '<span class="badge badge-warning">NS</span> ' : '') ?>
					    <?= (!empty($row['dns']['TXT']) ? '<span class="badge badge-default">TXT</span> ' : '') ?>
					    <?= (!empty($row['blacklist']) ? '<span class="badge badge-danger">DNSBL</span>' : '') ?>
					</td>
					<td><?= (!empty($row['screenshot_id']) ? '<i class="fa fa-camera" aria-hidden="true"></i> ' : '') ?></td>
				</tr>
				<?php $i++; endforeach ?>
			</tbody>
		</table>
	</div>
</div>
