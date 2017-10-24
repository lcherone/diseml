<?php 
	$meta = (array) $domain['meta'];
	$get_meta = function ($type = '') use ($meta) {
		foreach ((array) $meta['meta'] as $v) {
			if ((!empty($v['name']) && $v['name'] == $type) || (!empty($v['property']) && $v['property'] == $type)) {
				return $v['content'];
			}
		}
	};
?>

<h2><?= htmlentities($domain['domain']) ?></h2>

<div class="row">
	<div class="col-md-6">
		<!-- -->
		<div class="card">
			<div class="card-header" data-background-color="blue">
				<h4 class="title"><i class="fa fa-globe"></i> Screenshot</h4>
				<p class="category">Last Updated: <?= date_create($domain['screenshot']['added'])->format('jS M Y, g:ia') ?></p>
			</div>
			<div class="card-block">
				<div class="card-content">
					<img class="card-img-top" src="<?= trim($domain['screenshot']['path'], '.') ?>" alt="">
				</div>
			</div>
		</div>
	</div>	
	<div class="col-md-6">
		<!-- -->
		<div class="card">
			<div class="card-header" data-background-color="blue">
				<h4 class="title"><i class="fa fa-globe"></i> Details</h4>
				<p class="category">Last Updated: <?= date_create($domain['updated'])->format('jS M Y, g:ia') ?></p>
			</div>
			<div class="card-block">
				<div class="card-content">
					<table class="table table-sm table-list" style="width:100%">
						<tbody>

							<tr>
								<td>Additions</td>
								<td><?= htmlentities($domain['additions']) ?></td>
							</tr>				       
							<tr>
								<td>Entropy</td>
								<td><?= htmlentities($domain['entropy']) ?></td>
							</tr>				       			
							<tr>
								<td>Rank</td>
								<td><?= htmlentities($domain['rank']) ?></td>
							</tr>				       
							<tr>
								<td>DNS</td>
								<td>
									<?= (!empty($domain['dns_state']['A']) ? '<span class="badge badge-info">A</span> ' : '') ?>
									<?= (!empty($domain['dns_state']['MX']) ? '<span class="badge badge-success">MX</span> ' : '') ?>
									<?= (!empty($domain['dns_state']['CNAME']) ? '<span class="badge badge-info">CNAME</span> ' : '') ?>
									<?= (!empty($domain['dns_state']['NS']) ? '<span class="badge badge-warning">NS</span> ' : '') ?>
									<?= (!empty($domain['dns_state']['TXT']) ? '<span class="badge badge-default">TXT</span> ' : '') ?>
									<?= (!empty($domain['blacklist']) ? '<span class="badge badge-danger">DNSBL</span>' : '') ?>
								</td>
							</tr>
							<tr>
								<td>Whitelisted</td>
								<td><?= (!empty($domain['whitelist']) ?  '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>') ?></td>
							</tr>
							<?php if (!empty($domain['meta']['title'])): ?>
							<tr>
								<td>Title</td>
								<td><?= htmlentities($domain['meta']['title']) ?></td>
							</tr>
							<?php endif ?>
							<?php $t = $get_meta('description'); if (!empty($t)): ?>
							<tr>
								<td>Description</td>
								<td><?= htmlentities($t) ?></td>
							</tr>
							<?php endif ?>							
							<?php if (($t = $get_meta('author')) && !empty($t)): ?>
							<tr>
								<td>Author</td>
								<td><?= htmlentities($t) ?></td>
							</tr>
							<?php endif ?>							
							<?php if (($t = $get_meta('keywords')) && !empty($t)): ?>
							<tr>
								<td>Keywords</td>
								<td><?= htmlentities($t) ?></td>
							</tr>
							<?php endif ?>
							<?php if (!empty($domain['meta']['body'])): ?>
							<tr>
								<td>Body</td>
								<td><?= htmlentities(substr($domain['meta']['body'], 0, 300)) ?></td>
							</tr>
							<?php endif ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- -->
	</div>
	<div class="col-md-6">
		<!-- -->
		<div class="card">
			<div class="card-header" data-background-color="blue">
				<h4 class="title"><i class="fa fa-globe"></i> Geo IP Information</h4>
			</div>
			<div class="card-block">
				<div class="card-content">
					<table class="table table-sm table-list">
						<tbody>
							<?php if (!empty($domain['ip'])): ?>
							<tr>
								<td>IP Address</td>
								<td><?= htmlentities($domain['ip']) ?></td>
							</tr>
							<?php endif ?>
							<?php if (!empty($domain['ipinfo']['hostname'])): ?>
							<tr>
								<td>Hostname</td>
								<td><?= $domain['ipinfo']['hostname'] ?></td>
							</tr>
							<?php endif ?>
							<?php if (!empty($domain['ipinfo']['isp'])): ?>
							<tr>
								<td>ISP</td>
								<td><?= $domain['ipinfo']['isp'] ?></td>
							</tr>
							<?php endif ?>
							<?php if (!empty($domain['ipinfo']['as'])): ?>
							<tr>
								<td><em title="Autonomous System Number">AS Number</em></td>
								<td><?= $domain['ipinfo']['as'] ?></td>
							</tr>
							<?php endif ?>
							<?php if (!empty($domain['ipinfo']['as'])): ?>
							<tr>
								<td>Organization</td>
								<td><?= $domain['ipinfo']['org'] ?></td>
							</tr>
							<?php endif ?>						
							<?php if (false && !empty($domain['ipinfo']['iplong'])): ?>
							<tr>
								<td>IPlong</td>
								<td><?= $domain['ipinfo']['iplong'] ?></td>
							</tr>
							<?php endif ?>
							<?php if (!empty($domain['ipinfo']['city'])): ?>
							<tr>
								<td>City</td>
								<td><?= $domain['ipinfo']['city'] ?></td>
							</tr>
							<?php endif ?>

							<?php if (!empty($domain['ipinfo']['region_name'])): ?>
							<tr>
								<td>Region</td>
								<td><?= $domain['ipinfo']['region_name'] ?> (<?= $domain['ipinfo']['region'] ?>)</td>
							</tr>
							<?php endif ?>

							<?php if (!empty($domain['geoinfo']['country_name'])): ?>
							<tr>
								<td>Country</td>
								<td><?= $domain['geoinfo']['country_name'] ?> (<?= $domain['geoinfo']['country_code'] ?>)</td>
							</tr>
							<?php endif ?>

							<?php if (!empty($domain['ipinfo']['zip'])): ?>
							<tr>
								<td>Zip</td>
								<td><?= $domain['ipinfo']['zip'] ?></td>
							</tr>
							<?php endif ?>

							<?php if (!empty($domain['geoinfo']['continent_code'])): ?>
							<tr>
								<td>Continent</td>
								<td><?= $domain['geoinfo']['continent_code'] ?></td>
							</tr>
							<?php endif ?>

							<?php if (!empty($domain['geoinfo']['latitude'])): ?>
							<tr>
								<td>Latitude</td>
								<td><?= $domain['geoinfo']['latitude'] ?></td>
							</tr>
							<?php endif ?>

							<?php if (!empty($domain['geoinfo']['longitude'])): ?>
							<tr>
								<td>Longitude</td>
								<td><?= $domain['geoinfo']['longitude'] ?></td>
							</tr>
							<?php endif ?>

							<?php if (!empty($domain['ipinfo']['timezone'])): ?>
							<tr>
								<td>Timezone</td>
								<td><?= $domain['ipinfo']['timezone'] ?></td>
							</tr>
							<?php endif ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- -->
	</div>	
	<div class="col-md-6">
		<!-- -->
		<div class="card">
			<div class="card-header" data-background-color="blue">
				<h4 class="title"><i class="fa fa-globe"></i> DNS</h4>
			</div>
			<div class="card-block">
				<div class="card-content">
					<table class="table table-sm table-list">
						<thead>
							<tr>
								<th>Type</th>
								<th>Target</th>
								<th>TTL</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($domain['dns'] as $row): ?>
							<tr>
								<td><?= $row['type'] ?></td>
								<td><?= $row['ip'] ?><?= $row['target'] ?></td>
								<td><?= $row['ttl'] ?></td>
							</tr>
							<?php endforeach ?>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<!-- -->
	</div>
</div>
<?php ob_start() ?>
<?php $f3->set('javascript', $f3->get('javascript').ob_get_clean()) ?>
