<?php
namespace Module\Stats;

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
	    // is domain
	    if (!empty($params['action']) && $this->domains->validate($params['action'])) {
	    	
	        $domain = $this->domains->findOne('domain = ?', [$params['action']]);
	        
	        if (empty($domain->id)) {
	            $f3->error(404);
	        }

			//
	        $domain = $this->domains->process(trim($domain->domain));
	        
	       
			$domain = $this->domains->export($domain, true);
				
			// parse dns data
			//if (!empty($form['domain']['dns'])) {
			//	$form['domain']['dns'] = json_decode($form['domain']['dns']);
			//}
			
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
			
	        $f3->set('domain', $domain);
	        
	        $context = [
                'template' => 'app/template/default/template.php',
                'page' => [
                    'title' => 'View Domain',
                    'body' => $this->view->render('app/module/info/view/view.php')
                ]
            ];
	    } 
	    // index
	    else {
	    	if (isset($_SESSION['domains']['unprocessed'])) {
	    		unset($_SESSION['domains']['unprocessed']);
	    	}
	    	
	    	$limit = 10;
	    	
	    	$cache = \Cache::instance();
	    	
	    	if (!$cache->exists('top_domain', $result['top_domain'])) {
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
					LIMIT '.$limit.'
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
		    	$cache->set('top_domain', $result['top_domain'], 3600);
			}

	    	if (!$cache->exists('top_ip', $result['top_ip'])) {
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
					LIMIT '.$limit.'
		    	');	
		    	$cache->set('top_ip', $result['top_ip'], 3600);
	    	}
	    	
	    	if (!$cache->exists('top_country', $result['top_country'])) {
		    	$result['top_country'] = $this->domains->getAll('
					SELECT id, country, country_code, count(country) AS domains FROM ipinfo
					GROUP BY country HAVING domains > 1
					ORDER BY domains DESC LIMIT '.$limit.'
				');

		    	$cache->set('top_country', $result['top_country'], 3600);
	    	}

    		$f3->set('result', $result);
    		
    	    $context = [
                'template' => 'app/template/default/template.php',
                'page' => [
                    'title' => 'Example Module',
                    'body' => $this->view->render('app/module/stats/view/index.php')
                ]
            ];
	    }

        //
        $f3->mset($context);
	}

}