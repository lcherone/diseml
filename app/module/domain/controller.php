<?php
namespace Module\Domain;

class Controller extends \Framework\Controller
{
	/**
     *
     */
	public function __construct()
	{
		parent::__construct();

		// load models
		$this->domains = new \Model\Domains();
	}

	/**
	 *
	 */
	public function index(\Base $f3, $params)
	{
		$f3->set('PATH', '/stats');

		// is domain
		if (!empty($params['action']) && $this->domains->validate($params['action'])) {
		
			$cache = \Cache::instance();
            
            if (!$cache->exists('domain.'.md5($params['action']), $domain)) {

				$domain = $this->domains->findOne('domain = ?', [$params['action']]);
	
				if (empty($domain->id)) {
					$f3->error(404);
				}
	
				//
				//$domain = $this->domains->process(trim($domain->domain));

				// grab weather if not set
				if (empty($domain->screenshot->name)) {
					$screenshot = $this->domains->get_http_screenshot($domain->domain);
					if (!empty($screenshot->name)) {
						$domain->screenshot = $screenshot;
						$this->domains->store($domain);
						$domain = $domain->fresh();
					}
				}
	
				// grab weather if not set
				if (empty($domain->meta)) {
					$domain->meta = json_encode($this->domains->get_http_meta($domain->domain), JSON_PRETTY_PRINT);
	
					$this->domains->store($domain);
					$domain = $domain->fresh();
				}
	
				$domain = $this->domains->export($domain, true);
	
				// parse dns data
				if (!empty($domain['dns'])) {
					$domain['dns_state'] = json_decode($domain['dns'], true);
					// parse dns into flags
					if (is_array($domain['dns_state'])) {
						$dns = [
							'A' => false,
							'MX' => false,
							'CNAME' => false,
							'NS' => false,
							'TXT' => false
						];
						foreach ($domain['dns_state'] as $r) {
							foreach (array_keys($dns) as $type) {
								if (!empty($r['type']) && $r['type'] == $type) {
									$dns[$type] = true;
								}
							}
						}
						$domain['dns_state'] = $dns;
					}
				}
	
				if (!empty($domain['meta'])) {
					$domain['meta'] = json_decode($domain['meta'], true);
				}
				
				// dns
				if (!empty($domain['dns'])) {
					$domain['dns'] = (array) json_decode($domain['dns'], true);
				}
	
				// is MX only
				$domain['mx_record'] = 0;
				foreach ($domain['dns'] as $row) {
					if ($row['type'] == 'MX') {
						$domain['mx_record'] = 1;
					}
					if ($row['type'] == 'A') {
						$domain['a_record'] = 1;
					}
				}
	
				//
				$domain['rank'] = $this->domains->getCol('
					SELECT (
					   SELECT COUNT(*) 
					   FROM domains d2 
					   WHERE (d2.additions, d2.id) >= (d1.additions, d1.id)
					) AS rank
					FROM  domains d1
					WHERE domain = ? LIMIT 1
	    		', [$domain['domain']])[0];
	    		
    			$cache->set('domain.'.md5($params['action']), $domain, 10800);
			}

			$f3->set('domain', $domain);

			$context = [
				'template' => 'app/template/default/template.php',
				'page' => [
					'title' => 'View Domain',
					'body' => $this->view->render('app/module/domain/view/view.php')
				]
			];
		} 
		// index
		else {
			if (isset($_SESSION['domains']['unprocessed'])) {
				unset($_SESSION['domains']['unprocessed']);
			}

			$result['top_domain'] = $this->domains->getAll('
				SELECT *, (
				   SELECT COUNT(*) 
				   FROM domains d2 
				   WHERE (d2.additions, d2.id) >= (d1.additions, d1.id)
				) AS rank, (
				   SELECT country_name
				   FROM geoinfo d3 
				   WHERE d3.ip = d1.ip
				) AS country_name, (
				   SELECT country_code
				   FROM geoinfo d3 
				   WHERE d3.ip = d1.ip
				) AS country_code, (
				   SELECT isp
				   FROM ipinfo d3 
				   WHERE d3.ip = d1.ip
				) AS isp
				FROM domains d1 ORDER BY rank ASC
				LIMIT 10
	    	');
	    	
			foreach ($result['top_domain'] as $key => $row) {
				// parse stored dns info
				if (!empty($row['dns'])) {
					$row['dns'] = json_decode($row['dns'], true);
					// parse dns into flags
					if (is_array($row['dns'])) {
						$dns = [
							'A' => false,
							'MX' => false,
							'CNAME' => false,
							'NS' => false,
							'TXT' => false
						];
						foreach ($row['dns'] as $r) {
							foreach (array_keys($dns) as $type) {
								if (!empty($r['type']) && $r['type'] == $type) {
									$dns[$type] = true;
								}
							}
						}
						$row['dns'] = $dns;
						$result['top_domain'][$key] = $row;
					}
				}
			}

			$result['top_ip'] = $this->domains->getAll('
				SELECT ip, country_name, country_code, (
				   SELECT COUNT(*) 
				   FROM domains d2 
				   WHERE d2.geoinfo_id = d1.id
				) AS domains, (
				   SELECT isp
				   FROM ipinfo d3 
				   WHERE d3.ip = d1.ip
				) AS isp
				FROM geoinfo d1 ORDER BY domains DESC 
				LIMIT 10
	    	');	    	

			$f3->set('result', $result);

			$context = [
				'template' => 'app/template/default/template.php',
				'page' => [
					'title' => 'Example Module',
					'body' => $this->view->render('app/module/domain/view/index.php')
				]
			];
		}

		//
		$f3->mset($context);
	}

}
