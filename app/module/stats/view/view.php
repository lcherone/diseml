<?php
$dns = (array) json_decode($f3->view->raw($domain['dns']), true);

// is MX only
$domain['mx_record'] = 0;
foreach ($dns as $row) {
	if ($row['type'] == 'MX') {
		$domain['mx_record'] = 1;
	}
	if ($row['type'] == 'A') {
		$domain['a_record'] = 1;
	}
}
?>

<h2><?= htmlentities($domain['domain']) ?></h2>

<?php 
var_dump($domain);
?>

<div class="row mb-4">
	<div class="col-md-6">
		<div class="card">
			<div class="card-block">
				<h4 class="card-title p-2">Details</h4>
				<h6 class="card-subtitle mb-2 ml-2 text-muted">Last Updated: <?= (!empty($row['updated']) ? date_create($row['updated'])->format('jS M Y, g:ia') : '-') ?></h6>
				<table class="table table-sm">
				    <tbody>
				        <tr>
				            <td>IP Address</td>
				            <td><?= htmlentities($domain['ip']) ?></td>
				        </tr>
				        <tr>
				            <td>Additions</td>
				            <td><?= htmlentities($domain['additions']) ?></td>
				        </tr>				        <tr>
				            <td>Updated</td>
				            <td><?= htmlentities($domain['updated']) ?></td>
				        </tr>				        <tr>
				            <td>A Record</td>
				            <td><?= htmlentities($domain['a_record']) ?></td>
				        </tr>				        				        				        <tr>
				            <td>Entropy</td>
				            <td><?= htmlentities($domain['entropy']) ?></td>
				        </tr>				        <tr>
				            <td>Processed</td>
				            <td><?= htmlentities($domain['processed']) ?></td>
				        </tr>				        <tr>
				            <td>Rank</td>
				            <td><?= htmlentities($domain['rank']) ?></td>
				        </tr>				       
				        <tr>
				            <td>Records</td>
				            <td>
				                <?= (!empty($domain['a_record']) ? '<span class="badge badge-info">A</span> ' : '') ?>
				                <?= (!empty($domain['mx_record']) ? '<span class="badge badge-warning">MX</span> ' : '') ?>
				                <?= (empty($domain['a_record']) && !empty($domain['mx_record']) ? '<span class="badge badge-danger">MX Only</span> ' : '') ?>
				            </td>
				        </tr>
				        <tr>
				            <td>Blacklisted</td>
				            <td><?= (!empty($domain['blacklist']) ?  '<span class="badge badge-danger">Yes</span>' : '<span class="badge badge-success">No</span>') ?></td>
				        </tr>
				         <tr>
				            <td>Whitelisted</td>
				            <td><?= (!empty($domain['whitelist']) ?  '<span class="badge badge-success">Yes</span>' : '<span class="badge badge-danger">No</span>') ?></td>
				        </tr>
				    </tbody>
				</table>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card">
			<div class="card-block">
				<h4 class="card-title p-2">IP Info</h4>
				<h6 class="card-subtitle mb-2 ml-2 text-muted">Card subtitle</h6>
				<table class="table">
				    <tbody>
				        <?php foreach((array) $domain['ipinfo'] as $key => $value): 
				        if (empty($value) || in_array($key, ['id', 'ip',  'status',  'added'])) {continue;} ?>
				        <tr>
				            <td><?= ucfirst($key) ?></td>
				            <td><?= ucfirst($value) ?></td>
				        </tr>
				        <?php endforeach ?>
				    </tbody>
				</table>
			</div>
		</div>
		
		<div class="card">
			<div class="card-block">
				<h4 class="card-title p-2">Geo</h4>
				<h6 class="card-subtitle mb-2 ml-2 text-muted">Card subtitle</h6>
				<table class="table">
				    <tbody>
				        <?php foreach((array) $domain['geoinfo'] as $key => $value): 
				        if (empty($value) || in_array($key, ['id', 'credit', 'ip', 'request', 'added'])) {continue;} ?>
				        <tr>
				            <td><?= ucfirst($key) ?></td>
				            <td><?= ucfirst($value) ?></td>
				        </tr>
				        <?php endforeach ?>
				    </tbody>
				</table>
			</div>
		</div>

		<div class="card">
			<div class="card-block">
				<h4 class="card-title p-2">DNS</h4>
				<h6 class="card-subtitle mb-2 ml-2 text-muted">Card subtitle</h6>
				<table class="table">
				    <thead>
				        <tr>
				            <th>Type</th>
				            <th>Target</th>
				            <th>TTL</th>
				        </tr>
				    </thead>
				    <tbody>
				        <?php foreach($dns as $row): ?>
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
</div>

<div class="row">
	<div class="col-md-12">
		<div class="card">
			<div class="card-block">
				<h4 class="card-title p-2">Comments 45</h4>
				<h6 class="card-subtitle mb-2 ml-2 text-muted"></h6>
				<div class="card-text p-2">
					<style>
						.user_name{
							font-size:14px;
							font-weight: bold;
						}
						.comments-list .media {
							border-bottom: 1px dotted #ccc;
						}
					</style>
					<div class="comments-list">
					    
					    <div class="comment-item">
    						<div class="d-flex justify-content-start">
    							<div class="p-2"><a class="media-left" href="#"><img src="http://lorempixel.com/40/40/people/1/"></a></div>
    							<div class="p-2 user_name">Baltej Singh</div>
    							<div class="ml-auto p-2"><small>5 days ago</small></div>
    						</div>
    						<div class="media">
    							<div class="media-body p-2">
    								Wow! this is really great.
    								<p><small><a href="">Like</a> - <a href="">Share</a></small></p>
    							</div>
    						</div>
						</div>
						
						<div class="comment-form">
						    <form class="form-inline">
                              <label class="sr-only" for="inlineFormInput">Name</label>
                              <input type="text" class="form-control mb-2 mr-sm-2 mb-sm-0" id="inlineFormInput" placeholder="Jane Doe">
                            
                              <label class="sr-only" for="inlineFormInputGroup">Username</label>
                              <div class="input-group mb-2 mr-sm-2 mb-sm-0">
                                <div class="input-group-addon">@</div>
                                <input type="text" class="form-control" id="inlineFormInputGroup" placeholder="Username">
                              </div>
                              
                              <textarea id="multi" class="form-control<?= (!empty($form['errors']['multi']) ? ' form-control-invalid' : null) ?>" rows="7" name="multi" placeholder="enter each domain on a new line."><?= (!empty($form['values']['multi']) ? htmlentities($form['values']['multi']) : '') ?></textarea>
        

                              <button type="submit" class="btn btn-primary">Submit</button>
                            </form>
						</div>


						<div class="media">
							<p class="pull-right"><small>5 days ago</small></p>
							<a class="media-left" href="#">
								<img src="http://lorempixel.com/40/40/people/2/">
							</a>
							<div class="media-body">

								<h4 class="media-heading user_name">Baltej Singh</h4>
								Wow! this is really great.

								<p><small><a href="">Like</a> - <a href="">Share</a></small></p>
							</div>
						</div>
						<div class="media">
							<p class="pull-right"><small>5 days ago</small></p>
							<a class="media-left" href="#">
								<img src="http://lorempixel.com/40/40/people/3/">
							</a>
							<div class="media-body">

								<h4 class="media-heading user_name">Baltej Singh</h4>
								Wow! this is really great.

								<p><small><a href="">Like</a> - <a href="">Share</a></small></p>
							</div>
						</div>
						<div class="media">
							<p class="pull-right"><small>5 days ago</small></p>
							<a class="media-left" href="#">
								<img src="http://lorempixel.com/40/40/people/4/">
							</a>
							<div class="media-body">

								<h4 class="media-heading user_name">Baltej Singh</h4>
								Wow! this is really great.

								<p><small><a href="">Like</a> - <a href="">Share</a></small></p>
							</div>
						</div>
					</div>
				</div>
				<div class="p-2">
					<a href="#" class="card-link">Card link</a>
					<a href="#" class="card-link">Another link</a>
				</div>
			</div>
		</div>
	</div>
</div>
