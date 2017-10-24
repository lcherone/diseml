<?php ob_start() ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/2.8.0/css/flag-icon.min.css" integrity="sha256-EQjZwW4ljrt9dsonbyX+si6kbxgkVde47Ty9FQehnUg=" crossorigin="anonymous" />
<?php $f3->set('css', $f3->get('css').ob_get_clean()) ?>

<h3>Top Domains</h3>

<p>The following is ranked by how many times the domain has been submitted.</p>

<table class="table">
	<thead>
		<tr>
			<th>#</th>
			<th>Domain</th>
			<th>IP</th>
			<th>ISP</th>
			<th>Country</th>
			<th>DNS</th>
		</tr>
	</thead>
	<tbody>
		<?php 
		    foreach ($result['top_domain'] as $row):
		?>
		<tr>
			<td><?= $row['rank'] ?></td>
			<td><a href="/domain/<?= urlencode($row['domain']) ?>" class="ajax-link"><?= htmlentities($row['domain']) ?></a></td>
			<td><?= (!empty($row['ip']) ? '<a href="/ip/'.urlencode($row['ip']).'">'.htmlentities($row['ip']).'</a>' : null) ?></td>
			<td><?= (!empty($row['isp']) ? '<a href="/isp/'.urlencode($row['isp']).'">'.htmlentities($row['isp']).'</a>' : null) ?></td>
			<td><?= (!empty($row['country_code']) ? '<i class="flag-icon flag-icon-'.htmlentities(strtolower($row['country_code'])).'"></i> ' : null) ?><?= (!empty($row['country_name']) ? '<a href="/country/'.urlencode($row['country_name']).'">'.htmlentities($row['country_name']).'</a>' : null) ?></td>
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

<h3>Top IPs</h3>

<p>The following is ranked by how many domains the IP has associated.</p>

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
			<td><?= (!empty($row['ip']) ? '<a href="/ip/'.urlencode($row['ip']).'">'.htmlentities($row['ip']).'</a>' : null) ?></td>
			<td><?= (!empty($row['isp']) ? '<a href="/isp/'.urlencode($row['isp']).'">'.htmlentities($row['isp']).'</a>' : null) ?></td>
			<td><?= (!empty($row['country_code']) ? '<i class="flag-icon flag-icon-'.htmlentities(strtolower($row['country_code'])).'"></i> ' : null) ?><?= (!empty($row['country_name']) ? '<a href="/country/'.urlencode($row['country_name']).'">'.htmlentities($row['country_name']).'</a>' : null) ?></td>
			<td><?= htmlentities($row['domains']) ?></td>
		</tr>
		<?php $i++; endforeach ?>
	</tbody>
</table>

