<?php ob_start() ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/2.8.0/css/flag-icon.min.css" integrity="sha256-EQjZwW4ljrt9dsonbyX+si6kbxgkVde47Ty9FQehnUg=" crossorigin="anonymous" />
<?php $f3->set('css', $f3->get('css').ob_get_clean()) ?>

<div class="card card-nav-tabs">
	<div class="card-header" data-background-color="blue">
		<h4 class="title"><i class="fa fa-globe"></i> Top Domain’s</h4>
		<p class="category">The following is ranked by how many times the domain has been submitted.</p>
	</div>
	<div class="card-content">
		<table class="table">
			<thead>
				<tr>
					<th>#</th>
					<th>Domain</th>
					<th>IP</th>
					<th>ISP</th>
					<th>Country</th>
					<th>DNS</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($result['top_domain'] as $row): ?>
				<tr>
					<td><span title="<?= $row['additions'] ?> additions"><?= $row['rank'] ?></span></td>
					<td><a href="/domain/<?= urlencode($row['domain']) ?>" class="ajax-link"><?= htmlentities($row['domain']) ?></a></td>
					<td><?= (!empty($row['ip']) ? '<a href="/ip/'.urlencode($row['ip']).'" class="ajax-link">'.htmlentities($row['ip']).'</a>' : null) ?></td>
					<td><?= (!empty($row['isp']) ? '<a href="/isp/'.urlencode($row['isp']).'" class="ajax-link">'.htmlentities(ucwords($row['isp'])).'</a>' : null) ?></td>
					<td><?= (!empty($row['country_code']) ? '<i class="flag-icon flag-icon-'.htmlentities(strtolower($row['country_code'])).'"></i> ' : null) ?><?= (!empty($row['country_name']) ? '<a href="/country/'.urlencode($row['country_name']).'" class="ajax-link">'.htmlentities($row['country_name']).'</a>' : null) ?></td>
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
				<?php endforeach ?>
			</tbody>
		</table>
	</div>
</div>

<div class="card card-nav-tabs" >
	<div class="card-header" data-background-color="blue">
		<h4 class="title"><i class="fa fa-globe"></i> Top IP’s</h4>
		<p class="category">The following is ranked by how many domains the IP has associated.</p>
	</div>
	<div class="card-content">
		<table class="table">
			<thead>
				<tr>
					<th>#</th>
					<th>IP</th>
					<th>ISP</th>
					<th>Country</th>
					<th>Domains</th>
				</tr>
			</thead>
			<tbody>
				<?php $i=1; foreach ($result['top_ip'] as $row): ?>
				<tr>
					<td><?= $i ?></td>
					<td><?= (!empty($row['ip']) ? '<a href="/ip/'.urlencode($row['ip']).'" class="ajax-link">'.htmlentities($row['ip']).'</a>' : null) ?></td>
					<td><?= (!empty($row['isp']) ? '<a href="/isp/'.urlencode($row['isp']).'" class="ajax-link">'.htmlentities(ucwords($row['isp'])).'</a>' : null) ?></td>
					<td><?= (!empty($row['country_code']) ? '<i class="flag-icon flag-icon-'.htmlentities(strtolower($row['country_code'])).'"></i> ' : null) ?><?= (!empty($row['country_name']) ? '<a href="/country/'.urlencode($row['country_name']).'" class="ajax-link">'.htmlentities($row['country_name']).'</a>' : null) ?></td>
					<td><?= htmlentities($row['domains']) ?></td>
				</tr>
				<?php $i++; endforeach ?>
			</tbody>
		</table>
	</div>
</div>

<div class="card card-nav-tabs" >
	<div class="card-header" data-background-color="blue">
		<h4 class="title"><i class="fa fa-globe"></i> Top Country’s</h4>
		<p class="category">The following is ranked by how many domains the country has associated.</p>
	</div>
	<div class="card-content">
		<table class="table">
			<thead>
				<tr>
					<th>#</th>
					<th>Country</th>
					<th>Domains</th>
				</tr>
			</thead>
			<tbody>
				<?php $i=1; foreach ((array) $result['top_country'] as $row): ?>
				<tr>
					<td><?= $i ?></td>
					<td><?= (!empty($row['country_code']) ? '<i class="flag-icon flag-icon-'.htmlentities(strtolower($row['country_code'])).'"></i> ' : null) ?> <?= (!empty($row['country']) ? '<a href="/country/'.urlencode($row['country']).'" class="ajax-link">'.htmlentities($row['country']).'</a>' : null) ?></td>
					<td><?= (!empty($row['domains']) ? $row['domains'] : null) ?></td>
				</tr>
				<?php $i++; endforeach ?>
			</tbody>
		</table>
	</div>
</div>
