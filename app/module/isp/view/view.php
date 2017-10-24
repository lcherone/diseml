<?php ob_start() ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/2.8.0/css/flag-icon.min.css" integrity="sha256-EQjZwW4ljrt9dsonbyX+si6kbxgkVde47Ty9FQehnUg=" crossorigin="anonymous" />
<?php $f3->set('css', $f3->get('css').ob_get_clean()) ?>

<div class="card card-nav-tabs" id="port-forwards">
	<div class="card-header" data-background-color="blue">
		<h4 class="title">
			<i class="fa fa-globe"></i> ISP: <?= ucwords($f3->get('PARAMS.action')) ?>
			<a href="/isp/<?= $f3->get('PARAMS.action') ?>/json" class="pull-right mr-3 btn btn-primary">JSON</a>
			<a href="/isp/<?= $f3->get('PARAMS.action') ?>/txt" class="pull-right mr-3 btn btn-primary">TXT</a>
		</h4>
		<p class="category">All domains which this ISP hosts.</p>
	</div>
	<div class="card-content">
		<table class="table">
			<thead>
				<tr>
					<th>Domain</th>
					<th>IP</th>
					<th>Country</th>
					<th>DNS</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($result['domains'] as $row): ?>
				<tr>
					<td><a href="/domain/<?= urlencode($row['domain']) ?>"><?= htmlentities($row['domain']) ?></a></td>
					<td><?= (!empty($row['ip']) ? '<a href="/ip/'.urlencode($row['ip']).'">'.htmlentities($row['ip']).'</a>' : null) ?></td>
					<td>
						<?= (!empty($result['ipinfo'][$row['ipinfo_id']]['country_code']) ? '<i class="flag-icon flag-icon-'.htmlentities(strtolower($result['ipinfo'][$row['ipinfo_id']]['country_code'])).'"></i> ' : null) ?>
						<?= (!empty($result['ipinfo'][$row['ipinfo_id']]['country']) ? '<a href="/country/'.urlencode($result['ipinfo'][$row['ipinfo_id']]['country']).'">'.htmlentities($result['ipinfo'][$row['ipinfo_id']]['country']).'</a>' : null) ?>
					</td>
					<td>
					    <?= (!empty($row['dns']['A']) ? '<span class="badge badge-info">A</span> ' : '') ?>
					    <?= (!empty($row['dns']['MX']) ? '<span class="badge badge-success">MX</span> ' : '') ?>
					    <?= (!empty($row['dns']['CNAME']) ? '<span class="badge badge-info">CNAME</span> ' : '') ?>
					    <?= (!empty($row['dns']['NS']) ? '<span class="badge badge-warning">NS</span> ' : '') ?>
					    <?= (!empty($row['dns']['TXT']) ? '<span class="badge badge-default">TXT</span> ' : '') ?>
					    <?= (!empty($row['blacklist']) ? '<span class="badge badge-danger">DNSBL</span>' : '') ?>
					</td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
</div>
