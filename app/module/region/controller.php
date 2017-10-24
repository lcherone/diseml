<?php
namespace Module\Region;

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
        $this->ipinfo  = new \Framework\Model('ipinfo');
        $this->geoinfo  = new \Framework\Model('geoinfo');
    }

    /**
     *
     */
    public function index(\Base $f3, $params)
    {
        $f3->set('PATH', '/stats');
        
        // is domain
        if (!empty($params['action'])) {
                                    
            $cache = \Cache::instance();
            
            if (!$cache->exists('region.'.md5($params['action']), $result)) {
            
                $result['geoinfo'] = $this->geoinfo->findAll('region_name = ?', [$params['action']]);
                
                if (empty($result['geoinfo'])) {
                    $f3->error(404);
                }
                
                $result['ips'] = [];
                foreach ($result['geoinfo'] as $row) {
                    $result['ips'][] = $row->ip;
                }
                $result['ips'] = array_unique($result['ips']);
    
                $result['domains'] = [];
                foreach ($result['ips'] as $ip) {
                    foreach ((array) $this->domains->findAll('ip = ?', [$ip]) as $row) {
                        $result['domains'][] = $row;
                        //$result['geoinfo'][$row->geoinfo_id] = $row->geoinfo;
                        $result['ipinfo'][$row->ipinfo_id] = $row->ipinfo;
                    }
                }
    
                foreach ($result['domains'] as $id => $row) {
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
                                $result['domains'][$id] = $row;
                            }
                        }
                }
                
                // return diff type
                if (!empty($params['sub_action']) && in_array($params['sub_action'], ['json', 'txt'])) {
                    // process
                    $result = array_values($result['domains']);
                    foreach ($result as $key => $row) {
                        $result[$key] = $this->domains->export($row, true);
                        
                        // filter out what we want to hide
                        foreach ($result[$key] as $k => $v) {
                            if (in_array($k, ['id', 'ipinfo_id', 'geoinfo_id'])) {
                                unset($result[$key][$k]);
                            }
                            // clean ipinfo
                            if ($k == 'ipinfo') {
                                unset($v['id']);
                                $result[$key][$k] = $v;
                            }
                            // clean geoinfo
                            if ($k == 'geoinfo') {
                                unset($v['id']);
                                unset($v['credit']);
                                unset($v['request']);
                                unset($v['dma_code']);
                                $result[$key][$k] = $v;
                            }
                        }
                    }
                    
                    // return json
                    if ($params['sub_action'] == 'json') {
                        $this->json($result);
                    }
                    
                    // return txt
                    if ($params['sub_action'] == 'txt') {
                        header('Content-Type: text/plain;charset=utf-8');
                        foreach ($result as $row) {
                            echo $row['domain'].PHP_EOL;
                        }
                        exit;
                    }
                }
                $cache->set('region.'.md5($params['action']), $result, 3600);
			}

            $f3->set('result', $result);
            
            $context = [
                'template' => 'app/template/default/template.php',
                'page' => [
                    'title' => 'View IP',
                    'body' => $this->view->render('app/module/region/view/view.php')
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
                    'body' => $this->view->render('app/module/region/view/index.php')
                ]
            ];
        }

        //
        $f3->mset($context);
    }

}