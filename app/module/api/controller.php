<?php
namespace Module\Api;

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
        
        $this->cache = \Cache::instance();
    }

    /**
     *
     */
    public function index(\Base $f3, $params)
    {
        $f3->set('PATH', '/api');
        
        $endpoints = [
            [
                'description' => 'All Domains',
                'format' => 'JSON',
                'endpoint' => '/api/dump/json',
                'cache' => $this->cache->exists('api.dump.json')
            ],
            [
                'description' => 'All Domains',
                'format' => 'Text',
                'endpoint' => '/api/dump/list',
                'cache' => $this->cache->exists('api.dump.txt')
            ],
        ];
        
        $f3->set('endpoints', $endpoints);
            
        $context = [
            'template' => 'app/template/default/template.php',
            'page' => [
                'title' => 'API',
                'body' => $this->view->render('app/module/api/view/index.php')
            ]
        ];
        //
        $f3->mset($context);
    }
    
    /**
     * Validate domain/s
     * 
     * Should be able to handle:
     *  - As url param:  /api/validate/example.com
     *  - As POST param: domains['...', '...', ...]
     *  - Optional $_REQUEST['dns'] with [mx,a] to check valid DNS
     * 
     */
    public function validate(\Base $f3, $params)
    {
        set_time_limit(5);
        
        $domains = [];
        
        // handle url param
        if ($f3->exists('PARAMS.sub_action', $domain)) {
            $domains[] = $domain;
        }
        
        // handle post domain/s
        if ($f3->exists('REQUEST.domain', $domain)) {
            if (is_array($domain)) {
                $domains = $domain;
            } else {
                $domains[] = $domain;
            }
        }
        
        // handle dns requirement
        if ($f3->exists('REQUEST.dns', $dns)) {
            $dns = strtoupper($dns);
            
            if (!in_array($dns, ['MX', 'A'])) {
                $dns = 'MX';
            }
        }

        // validate
        if (!empty($domains)) {
            $validated = [];
            foreach ((array) $domains as $domain) {
                // validate with dns
                if ($dns !== null) {
                    $valid = $this->domains->validate($domain, false, $dns);
                } 
                // without dns
                else {
                    
                    $valid = $this->domains->validate($domain);
                }
                
                if (!$valid) {
                    $validated['invalid'][] = $f3->clean($domain);
                } else {
                    $validated['valid'][] = $valid;
                }
            }
            
            $response = [
                'status' => 'success',
                'dns'    => (string) $dns,
                'result' => $validated
            ];
        } else {
            $response = [
                'status' => 'error',
                'error' => 'Missing domain/s param.'
            ];
        }
        
        $this->json($response);
    }
    
    /**
     * add domain/s
     * 
     * Should be able to handle:
     *  - As url param:  /api/add/example.com
     *  - As POST param: domains['...', '...', ...]
     * 
     */
    public function submit(\Base $f3, $params)
    {
        $this->add($f3, $params);
    }
    public function add(\Base $f3, $params)
    {
        set_time_limit(5);
        
        $domains = [];
        
        // handle url param
        if ($f3->exists('PARAMS.sub_action', $domain)) {
            $domains[] = $domain;
        }
        
        // handle post domain/s
        if ($f3->exists('REQUEST.domain', $domain)) {
            if (is_array($domain)) {
                $domains = $domain;
            } else {
                $domains[] = $domain;
            }
        }

        //
        if (!empty($domains)) {
            
            // validate
            $validated = [
                'valid' => [],
                'invalid' => []
            ];
            foreach ((array) $domains as $domain) {
                // validate
                $valid = $this->domains->validate($domain);

                if (!$valid) {
                    $validated['invalid'][] = $f3->clean($domain);
                } else {
                    $validated['valid'][] = $valid;
                }
            }
            
            $domains = $validated;
            
            // add
            if (!empty($domains['valid'])) {
                foreach ($domains['valid'] as $key => $domain) {
                    // process domain
                    $domains['valid'][$key] = $this->domains->process($domain);
                    
                    // parse stored dns info
                    if (!empty($domains['valid'][$key]['dns'])) {
                        $domains['valid'][$key]['dns'] = json_decode($domains['valid'][$key]['dns'], true);
                        // parse dns into flags
                        if (is_array($domains['valid'][$key]['dns'])) {
                            $dns = [
                                'A' => false,
                                'MX' => false,
                                'CNAME' => false,
                                'NS' => false,
                                'TXT' => false
                            ];
                            foreach ($domains['valid'][$key]['dns'] as $row) {
                                foreach (array_keys($dns) as $type) {
                                    if (!empty($row['type']) && $row['type'] == $type) {
                                        $dns[$type] = true;
                                    }
                                }
                            }
                            $domains['valid'][$key]['dns'] = $dns;
                            
                            // got no dns or ip, domain no longer active
                            if (
                                empty($domains['valid'][$key]['ip']) &&
                                !$this->audit->ipv4($domains['valid'][$key]['ip']) &&
                                !$this->audit->ispublic($domains['valid'][$key]['ip']) && !in_array(true, $dns, true)
                            ) {
                                unset($domains['valid'][$key]['dns']);
                                $domains['invalid'][] = $domain;
                                $this->domains->trash($domains['valid'][$key]);
                                continue;
                            }
                        } else {
                            unset($domains['valid'][$key]['dns']);
                            $domains['invalid'][] = $domain;
                            $this->domains->trash($domains['valid'][$key]);
                            continue;
                        }
                    } else {
                        unset($domains['valid'][$key]['dns']);
                        $domains['invalid'][] = $domain;
                        $this->domains->trash($domains['valid'][$key]);
                        continue;
                    }
                    
                    // parse stored ip info
                    if (!empty($domains['valid'][$key]['ipinfo_id'])) {
                        $domains['valid'][$key]['ipinfo'] = $domains['valid'][$key]->ipinfo;
                        // remove id
                        if (!empty($domains['valid'][$key]['ipinfo']['id'])) {
                            unset($domains['valid'][$key]['ipinfo']['id']);
                        }
                        // remove reference id
                        unset($domains['valid'][$key]['ipinfo_id']);
                    }
                    
                    // parse stored geo info
                    if (!empty($domains['valid'][$key]['geoinfo_id'])) {
                        $domains['valid'][$key]['geoinfo'] = $domains['valid'][$key]->geoinfo;
                        // remove id
                        if (!empty($domains['valid'][$key]['geoinfo']['id'])) {
                            unset($domains['valid'][$key]['geoinfo']['id']);
                        }
                        // remove reference id
                        unset($domains['valid'][$key]['geoinfo_id']);
                    }

                    // unset ids
                    unset($domains['valid'][$key]['id']);

                    // filter out response columns
                    if ($f3->exists('REQUEST.columns', $columns)) {
                        if (is_array($columns)) {
                            $filtered = [];
                            foreach ($columns as $col) {
                                $filtered[$col] = !empty($domains['valid'][$key][$col]) ? $domains['valid'][$key][$col] : null;
                            }
                            $domains['valid'][$key] = $filtered;
                        } else {
                            $filtered = [];
                            $filtered[$columns] = !empty($domains['valid'][$key][$columns]) ? $domains['valid'][$key][$columns] : null;
                            $domains['valid'][$key] = $filtered;
                        }
                    }
                }
            }
            
            $response = [
                'status' => 'success',
                'result' => $domains
            ];
        } else {
            $response = [
                'status' => 'error',
                'error' => 'Missing domain/s param.'
            ];
        }
        
        $this->json($response);
    }
    
    /**
     *
     */
    public function check(\Base $f3, $params)
    {
        $domain = (!empty($params['sub_action']) ? $params['sub_action'] : null);
        
        if (empty($domain)) {
            $this->json([
                'status' => 'error',
                'msg' => 'Domain required.'
            ]);
        }
        
        switch ($params['sub_action_id']) {
            case "txt":
            case "list":
                if (!$this->cache->exists('api.check.txt', $result)) {
                    $result = !empty($this->domains->count('domain = ? LIMIT 1', [$domain]));
                    $this->cache->set('api.check.txt', $result, 1800);
    			}
                
                header('Content-Type: text/plain;charset=utf-8');
                exit($result);
                break;
            case "json":
                if (!$this->cache->exists('api.dump.json', $result)) {
                    $result = !empty($this->domains->count('domain = ? LIMIT 1', [$domain]));
                    $this->cache->set('api.dump.json', $result, 1800);
    			}
                $this->json($result);
                break;
            default:
                $f3->reroute('/api/check/'.$domain.'/json');
                break;
        }
    }
    
    public function data(\Base $f3, $params)
    {
        $this->info($f3, $params);
    }
    
    /**
     *
     */
    public function info(\Base $f3, $params)
    {
        $domain = (!empty($params['sub_action']) ? $params['sub_action'] : null);
        
        if (empty($domain)) {
            $this->json([
                'status' => 'error',
                'msg' => 'Domain required.'
            ]);
        }

        if (!$this->cache->exists('api.info.'.md5($params['sub_action']), $domain)) {
            $domain = $this->domains->findOne('domain = ?', [$domain]);
            
            if (empty($domain)) {
                $this->json([
                    'status' => 'error',
                    'msg' => 'Domain not found.'
                ]);
            }
            
            unset($domain->id);
            
            $domain = $this->domains->export($domain, true);
            
            //
            $domain['dns'] = (array) json_decode($domain['dns'], true);
    
            // remove ids
            unset($domain['id']);
            unset($domain['ipinfo']['id']);
            unset($domain['geoinfo']['id']);
            unset($domain['ipinfo_id']);
            unset($domain['geoinfo_id']);
            //remove ips
            unset($domain['ipinfo']['ip']);
            unset($domain['geoinfo']['ip']);
        
            $this->cache->set('country.'.md5($params['action']), $result, 3600);
		}
        
        $this->json($domain);
    }

    /**
     *
     */
    public function dump(\Base $f3, $params)
    {
        switch ($params['sub_action']) {
            case "txt":
            case "list":
                if (!$this->cache->exists('api.dump.txt', $result)) {
                    $result = $this->domains->getAll('SELECT domain FROM domains ORDER BY domain ASC');
                    $this->cache->set('api.dump.txt', $result, 1800);
    			}
                
                header('Content-Type: text/plain;charset=utf-8');
                foreach ($result as $row) {
                    echo $row['domain'].PHP_EOL;
                }
                exit;
                break;
            case "json":
                if (!$this->cache->exists('api.dump.json', $result)) {
                    $result = $this->domains->getCol('SELECT domain FROM domains ORDER BY domain ASC');
                    $this->cache->set('api.dump.json', $result, 1800);
    			}
                $this->json($result);
                break;
            default:
                $f3->reroute('/api/dump/list');
                break;
        }
    }

}