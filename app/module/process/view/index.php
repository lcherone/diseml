<?php ob_start() ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/2.8.0/css/flag-icon.min.css" integrity="sha256-EQjZwW4ljrt9dsonbyX+si6kbxgkVde47Ty9FQehnUg=" crossorigin="anonymous" />
<?php $f3->set('css', $f3->get('css').ob_get_clean()) ?>

<div class="card card-nav-tabs" id="port-forwards">
	<div class="card-header" data-background-color="blue">
		<h4 class="title" id="processing-title">
			<i class="fa fa-spinner fa-spin"></i> Processing</span>
			<div class="text-right" style="margin-top:-25px;margin-right:5px">
				<div class="signal-bars mt1 sizing-box good four-bars">
					<div class="first-bar bar"></div>
					<div class="second-bar bar"></div>
					<div class="third-bar bar"></div>
					<div class="fourth-bar bar"></div>
					<div class="fifth-bar bar"></div>
				</div>
			</div>
		</h4>
		<p class="category" id="processing-category"><span id="domains_processed">0</span> of <span id="domains_submitted"><?= count($form['domains']) ?></span><span id="current_domain"></p>
	</div>
	<div class="card-content">
		<div id="processing-table"<?= (!empty($form['domains']) ? ' data-process="1"' : null) ?>>
			<?php if (!empty($_SESSION['errors']['global'])): ?>
			<div class="alert alert-info">
				<a href="#" class="close" data-dismiss="alert">&times;</a>
				<?= $this->raw($_SESSION['errors']['global']) ?>
			</div>
			<?php unset($_SESSION['errors']); endif ?>
			
			<h5>Progress</h5>
			<div class="progress">
				<div id="process-progress" class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
			</div>
			<table class="table table-small">
				<thead>
					<tr>
						<th><i class="fa fa-check" aria-hidden="true"></i> Valid</th>
						<th><i class="fa fa-times" aria-hidden="true"></i> Invalid</th>
						<th><i class="fa fa-upload" aria-hidden="true"></i> Added</th>
						<th><i class="fa fa-thumbs-up" aria-hidden="true"></i> Up</th>
						<th><i class="fa fa-thumbs-down" aria-hidden="true"></i> Down</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><span id="domains_valid">0</span></td>
						<td><span id="domains_invalid">0</span></td>
						<td><span id="domains_added">0</span> </td>
						<td><span id="domains_up">0</span></td>
						<td><span id="domains_down">0</span></td>
					</tr>
				</tbody>
			</table>
			<br>
			<h5>Domains</h5>
			<table class="table table-small">
				<thead>
					<tr>
						<th>Domain</th>
						<th>IP</th>
						<th>ISP</th>
						<th>Country</th>
						<th>DNS</th>
					</tr>
				</thead>
				<tbody>
					<?php $id = 1; foreach ($form['domains'] as $domain): ?>
					<tr data-domain="<?= htmlentities(strtolower($domain)) ?>" data-id="<?= $id ?>">
						<td class="text"><?= htmlentities(strtolower($domain)) ?></td>
						<td class="text">-</td>
						<td class="text">-</td>
						<td class="text">-</td>
						<td>-</td>
					</tr>
					<?php $id++; endforeach ?>
				</tbody>
			</table>
		</div>
	</div>
</div>

<?php ob_start() ?>
<script>
	$(document).ready(function() {
		var csrf = '<?= $csrf ?>';
		var timer = 0;
		var totals = {
			submitted: <?= count($form['domains']) ?>,
			added: 0,
			processed: 0,
			valid: 0,
			invalid: 0,
			up: 0,
			down: 0,
			requests: 0
		};

        //
		function removeDomain(arr) {
			var what, a = arguments, L = a.length, ax;
			while (L > 1 && arr.length) {
				what = a[--L];
				while ((ax= arr.indexOf(what)) !== -1) {
					arr.splice(ax, 1);
				}
			}
			return arr;
		}

		function update_totals() {
			$.each(totals, function(index, row) {
				$('#domains_'+index).text(row);
			});

			var complete = Math.round((totals.processed * 100) / totals.submitted);
			$('#process-progress').attr('style', 'width:'+complete.toString() + '%').text(complete.toString() + '%');

			if (complete == 100) {
				$('#domains_requests').text(0);
				$('#process-progress').fadeOut();
				if ($('#processing-title').html() != 'Processing complete!') {
					$('#processing-title').html('Processing complete!');
					$('#processing-category').html('<p>Thank you for your submission.</p>');
				}
			}

			if (totals.requests <= 10 && totals.requests > 0) {
				$('.signal-bars').removeClass('bad ok').addClass('good').removeClass('four-bars three-bars two-bars one-bar').addClass('five-bars');
			} else if (totals.requests <= 15 && totals.requests > 10) {
				$('.signal-bars').removeClass('bad ok').addClass('good').removeClass('five-bars three-bars two-bars one-bar').addClass('four-bars');
			} else if (totals.requests <= 20 && totals.requests > 15) {
				$('.signal-bars').removeClass('good bad').addClass('ok').removeClass('five-bars four-bars two-bars one-bar').addClass('three-bars');
			} else if (totals.requests <= 25 && totals.requests > 20) {
				$('.signal-bars').removeClass('good bad').addClass('ok').removeClass('five-bars four-bars three-bars one-bar').addClass('two-bars');
			} else if (totals.requests <= 50 && totals.requests > 25) {
				$('.signal-bars').removeClass('good ok').addClass('bad').removeClass('five-bars four-bars three-bars two-bars').addClass('one-bar');
			} else {
				$('.signal-bars').removeClass('good ok').addClass('bad').removeClass('five-bars four-bars three-bars two-bars').addClass('one-bar');
			}
		}

		function add_domains(i, domains) {
			
			$.each(domains, function(index, row) {
				$('#current_domain').text(': '+row);
			});

			promise[i] = $.ajax({ 
				url: "/api/add",
				type: 'POST',
				data: {
					csrf: csrf,
					domain: domains,
					columns: ['domain', 'ip', 'ipinfo', 'dns']
				},
				dataType: 'json',
				beforeSend: function(jqXHR) {
					$.xhrPool.push(jqXHR);
					totals.requests++;
				},
				complete: function(jqXHR) {
					var i = $.xhrPool.indexOf(jqXHR);
					if (i > -1) $.xhrPool.splice(i, 1);
					totals.requests--;
				},
				error: function() {
					totals.requests = 0;
					$.xhrPool.abortAll();
				}
			});
			promise[i].then(function(data) {
				// valid
				$.each(data.result.valid, function(index, row) {
					totals.processed++;
					totals.added++;
					totals.valid++;
					totals.up++;
					
					// update table
					dtr[i] = table.find('[data-domain="'+row.domain+'"]');
					dtr[i].children('td:eq(0)').html('<span><a href="/domain/'+row.domain+'">'+row.domain+'</span></a></span>');
					dtr[i].children('td:eq(1)').html('<span title="'+(row.ip ? row.ip : '')+'">'+(row.ip ? '<a href="/ip/'+row.ip+'">'+row.ip+'</a>' : '-')+'</span>');
					dtr[i].children('td:eq(2)').html('<span title="'+(row.ipinfo && row.ipinfo.isp ? row.ipinfo.isp : '')+'">'+(row.ipinfo && row.ipinfo.isp ? '<a href="/isp/'+row.ipinfo.isp+'">'+row.ipinfo.isp+'</a>' : '-')+'</span>');
					dtr[i].children('td:eq(3)').html('<span title="'+(row.ipinfo && row.ipinfo.country ? row.ipinfo.country : '-')+'">'+(row.ipinfo && row.ipinfo.country_code ? '<i class="flag-icon flag-icon-'+row.ipinfo.country_code.toLowerCase()+' "></i> ' : '')+(row.ipinfo && row.ipinfo.country ? '<a href="/country/'+row.ipinfo.country+'">'+row.ipinfo.country+'</a>' : '-')+'</span>');
					dtr[i].children('td:eq(4)').html(
						(row.dns && row.dns.A ? '<span class="badge badge-info">A</span> ' : '') +
						(row.dns && row.dns.MX ? '<span class="badge badge-success">MX</span> ' : '') +
						(row.dns && row.dns.CNAME ? '<span class="badge badge-info">CNAME</span> ' : '') +
						(row.dns && row.dns.NS ? '<span class="badge badge-warning">NS</span> ' : '') +
						(row.dns && row.dns.TXT ? '<span class="badge badge-default">TXT</span>' : '') +
						(row.dns && row.blacklist == 1 ? '<span class="badge badge-danger">DNSBL</span>' : '')
					);
				});
				update_totals();

				// invalid
				$.each(data.result.invalid, function(index, row) {
					totals.processed++;
					totals.invalid++;
					totals.down++;

					dtr[i] = table.find('[data-domain="'+row+'"]');
					dtr[i].addClass('table-danger');
				});

				update_totals();
			});
		}

		function validate_domains(i, domains) {
			promise[i] = $.ajax({ 
				url: "/api/validate",
				type: 'POST',
				data: {
					csrf: csrf,
					domain: domains
				},
				dataType: 'json',
				beforeSend: function(jqXHR) {
					$.xhrPool.push(jqXHR);
					totals.requests++;
				},
				complete: function(jqXHR) {
					var i = $.xhrPool.indexOf(jqXHR);
					if (i > -1) $.xhrPool.splice(i, 1);
					totals.requests--;
				},
				error: function() {
					totals.requests = 0;
					$.xhrPool.abortAll();
				}
			});
			promise[i].then(function(data) {
				// valid
				$.each(data.result.valid, function(index, row) {
					totals.valid++;
				});
				update_totals();

				// invalid
				$.each(data.result.invalid, function(index, row) {
					totals.invalid++;
					totals.valid--;
					removeDomain(domains, row);

					dtr[i] = table.find('[data-domain="'+row+'"]');
					dtr[i].addClass('table-danger');
				});
				update_totals();

				if (domains.length > 0) {
					add_domains(i, domains);
				}
			});
		}

        //
		var table = $(document).find('#processing-table');

		if (table.data('process') == 1) {
			var domain_tr = $(document).find('[data-domain]');
			var promise = [];
			var dtr = [];
			var size = 15;
			var batch = [];
			for (var i=0; i<domain_tr.length; i+=size) {
				// current batch
				batch = domain_tr.slice(i, i+size);

				// run batch
				setTimeout(function(batch) {
					// extract out all domains
					var domains = [];
					batch.each(function(index, row) {
						domains.push($(this).data('domain'));
					});

					//validate_domains(i, domains);
					add_domains(i, domains);
				}, timer, batch);
				timer += 500;
			}
		}
	});
</script>
<?php $f3->set('javascript', $f3->get('javascript').ob_get_clean()) ?>
