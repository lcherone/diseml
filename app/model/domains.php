<?php
namespace Model;

class Domains extends \Framework\Model
{
    protected $f3;

    /**
     *
     */
    public function __construct()
    {
        parent::__construct('domains');

        // framework
        $this->f3    = \Base::instance();
        $this->audit = \Audit::instance();
    }

    /**
     * Validates domain name, returns tld domain or false
     *
     * @param string $url
     * @param bool $basic
     *
     * @return mixed
     */
    public function validate($domain = null, $fast = true, $dns = 'MX') {
        
        $valid = false;

        $url = parse_url(filter_var($domain, FILTER_SANITIZE_URL));

        if (!isset($url['host'])) {
            $url['host'] = $url['path'];
        }

        if ($url['host'] != '') {
            /* scheme not found, set default */
            if (!isset($url['scheme'])) {
                $url['scheme'] = 'http';
            }

            // basic
            if (strpos($url['scheme'].'://'.$url['host'], '.') === false) {
                return false;
            }
            
            if (!filter_var($url['scheme'].'://'.$url['host'], FILTER_VALIDATE_URL)) {
                return false;
            }

            // is_string(filter_var(url, FILTER_VALIDATE_URL))
            if (!$this->audit->url($url['scheme'].'://'.$url['host'])) {
                return false;
            }

            // fast mode dont do dns check
            if ($fast) {
                return $url['host'];
            }
            
            putenv('RES_OPTIONS=retrans:1 retry:1 timeout:1 attempts:1');

            // strict validation
            if (
                in_array($url['scheme'], ['http', 'https']) &&
                ip2long($url['host']) === false &&
                checkdnsrr($url['host'], $dns) // &&
                //@get_headers($url)
            ) {
                $valid = $url['host'];
            }
        }

        return $valid;
    }

    /**
     *
     */
    public function process($domain = null, $update = false)
    {
        $domain = $this->findOrCreate([
            'domain' => (string) $domain
        ]);

        // process
        if (empty($domain->processed) || $update) {
            putenv('RES_OPTIONS=retrans:1 retry:1 timeout:1 attempts:1');
            // get ip
            $domain->ip = @gethostbyname($domain->domain);
            $domain->aName = ($domain->ip === $domain->domain) ? 0 : 1;
            //
            // blacklisted
            /*
             all.s5h.net	 b.barracudacentral.org	 bl.emailbasura.org
 bl.spamcannibal.org	 bl.spamcop.net	 blacklist.woody.ch
 bogons.cymru.com	 cbl.abuseat.org	 cdl.anti-spam.org.cn
 combined.abuse.ch	 db.wpbl.info	 dnsbl-1.uceprotect.net
 dnsbl-2.uceprotect.net	 dnsbl-3.uceprotect.net	 dnsbl.anticaptcha.net
 dnsbl.cyberlogic.net	 dnsbl.dronebl.org	 dnsbl.inps.de
 dnsbl.sorbs.net	 dnsbl.spfbl.net	 drone.abuse.ch
 duinv.aupads.org	 dul.dnsbl.sorbs.net	 dyna.spamrats.com
 dynip.rothen.com	 exitnodes.tor.dnsbl.sectoor.de	 http.dnsbl.sorbs.net
 ips.backscatterer.org	 ix.dnsbl.manitu.net	 korea.services.net
 misc.dnsbl.sorbs.net	 noptr.spamrats.com	 orvedb.aupads.org
 pbl.spamhaus.org	 proxy.bl.gweep.ca	 psbl.surriel.com
 relays.bl.gweep.ca	 relays.nether.net	 sbl.spamhaus.org
 short.rbl.jp	 singular.ttk.pte.hu	 smtp.dnsbl.sorbs.net
 socks.dnsbl.sorbs.net	 spam.abuse.ch	 spam.dnsbl.anonmails.de
 spam.dnsbl.sorbs.net	 spam.spamrats.com	 spambot.bls.digibase.ca
 spamrbl.imp.ch	 spamsources.fabel.dk	 ubl.lashback.com
 ubl.unsubscore.com	 virus.rbl.jp	 web.dnsbl.sorbs.net
 wormrbl.imp.ch	 xbl.spamhaus.org	 z.mailspike.net
 zen.spamhaus.org	 zombie.dnsbl.sorbs.net	*/
 
            $this->f3->set('DNSBL', 'bl.spamcop.net,blacklist.sci.kun.nl,proxy.bl.gweep.ca');
            $domain->blacklist = $this->f3->blacklisted($domain->ip);
            $this->f3->set('DNSBL', '');
            //
            // geo info
            if (
                !empty($domain->ip) &&
                $this->audit->ipv4($domain->ip) &&
                $this->audit->ispublic($domain->ip)
            ) {
                // ip info
                $domain->ipinfo = $this->get_ip_info($domain->ip);
                //
                // geo info
                $domain->geoinfo = $this->get_geo_info($domain->ip);
            }
            
            // dns info - has api limit so dont do here
            $domain->dns = json_encode($this->get_dns_info($domain->domain));

            $domain->entropy = $this->audit->entropy($domain->domain);
            $domain->processed = 1;

            if (empty($domain->id)) {
                $domain->processed = false;
                $domain->additions = 1;
                $domain->whitelisted = 0;
                $domain->added = date_create();
            }
        }
        
        if (!empty($domain->id)) {
            $domain->additions++;
            $domain->updated = date_create();
        }
        
        $this->store($domain);

        return $domain->fresh();
    }
    
    /**
     *
     */
    public function add($domain = null)
    {
        $domain = $this->findOrCreate([
            'domain' => (string) $domain
        ]);

        if (!empty($domain->id)) {
            $domain->additions++;
            $domain->updated = date_create();
        } else {
            $domain->processed = false;
            $domain->additions = 1;
            $domain->whitelisted = 0;
            $domain->added = date_create();
        }

        $this->store($domain);

        return $domain->fresh();
    }

    /**
     *
     */
    public function split($str = '')
    {
        return array_filter(array_map('trim', explode(PHP_EOL, $str)));
    }
    
    /**
     * Get dns info
     */
    public function get_dns_info($domain = null)
    {
        if (empty($domain)) {
            return null;
        }
        
        putenv('RES_OPTIONS=retrans:1 retry:1 timeout:1 attempts:1');
        
        return (array) dns_get_record(
            $domain, DNS_A + DNS_MX + DNS_CNAME + DNS_NS + DNS_PTR + DNS_TXT
        );
    }

    /**
     * Get IP info from ip address
     */
    public function get_ip_info($ip = null)
    {
        if ($ip === null) {
            return [];
        }

        $this->table = 'ipinfo';

        $ipinfo = $this->findOrCreate([
            'ip' => (string) $ip
        ]);

        // if we dont have a status query the API
        if (empty($ipinfo['status'])) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://ip-api.com/json/'.$ip);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);

            $data = json_decode($data, true);

            putenv('RES_OPTIONS=retrans:1 retry:1 timeout:1 attempts:1');

            $ipinfo['ip']       = $ip;
            $ipinfo['hostname'] = @gethostbyaddr($data['query']);
            $ipinfo['isp']      = @ucwords($data['isp']);
            $ipinfo['as']       = @$data['as'];
            $ipinfo['city']     = @$data['city'];
            $ipinfo['country']  = @ucwords($data['country']);
            $ipinfo['country_code'] = @$data['countryCode'];
            $ipinfo['lat']      = @$data['lat'];
            $ipinfo['lon']      = @$data['lon'];
            $ipinfo['org']      = @$data['org'];
            $ipinfo['region']   = @$data['region'];
            $ipinfo['region_name'] = @$data['regionName'];
            $ipinfo['timezone'] = @$data['timezone'];
            $ipinfo['zip']      = @$data['zip'];
            $ipinfo['iplong']   = @ip2long(@$data['query']);
            $ipinfo['status']   = (@$data['status'] == 'success' ? 'success' : 'error');
            $ipinfo['added']    = date_create();

            $this->store($ipinfo);

            $ipinfo = $ipinfo->fresh();
        }

        $this->table = 'domains';

        return $ipinfo;
    }

    /**
     *
     */
    public function get_geo_info($ip = null)
    {
        if ($ip === null) {
            return null;
        }

        $this->table = 'geoinfo';

        $geoinfo = $this->findOrCreate([
            'ip' => (string) $ip
        ]);

        //
        if (empty($geoinfo->processed)) {
            $geo = \Web\Geo::instance();

            $location = $geo->location($ip);

            $geoinfo->import(
                (array) $location
            );

            $geoinfo->added = date_create();

            $this->store($geoinfo);

            $geoinfo = $geoinfo->fresh();
        }

        $this->table = 'domains';

        return $geoinfo;
    }
    
    /**
     * 
     */
    public function get_http_meta($domain = '', $scheme = 'http')
    {
        if (empty($domain)) {
            return [
                'error' => 'Empty domain'    
            ];
        }
        
        if (!$this->validate($domain)) {
            return [
                'error' => 'Invalid domain'    
            ];
        }
        
        //
		$request = \Web::instance()->request($scheme.'://'.$domain);
		
		if (empty($request['body']) && !empty($request['error'])) {
			exit($request['error']);
		} elseif (empty($request['body']) && !empty($request['headers'][0])) {
			exit($request['headers'][0]);
		}

		// init dom
		libxml_use_internal_errors(true);
		$html = new \DOMDocument();
		$html->loadHTML(@$request['body']);
		$html->preserveWhiteSpace = false;
        $html->strictErrorChecking = false;
        libxml_clear_errors();
        
        // get title
		$meta = [];
		foreach ($html->getElementsByTagName('title') as $elm) {
			$meta['title'] = $elm->nodeValue;
		}

		// get meta tags
		foreach ($html->getElementsByTagName('meta') as $elm) {
			//
			$name     = $elm->getAttribute('name');
			$property = $elm->getAttribute('property');
			$content  = $elm->getAttribute('content');
			//
			$meta['meta'][] = [
				'name' => preg_replace('/\s+/', ' ', $name),
				'property' => preg_replace('/\s+/', ' ', $property),
				'content' => preg_replace('/\s+/', ' ', $content)
			];
		}
		
		// get body
		foreach ($html->getElementsByTagName('body') as $elm) {
			// remove style
			foreach (iterator_to_array($elm->getElementsByTagName("style")) as $style) {
				$style->parentNode->removeChild($style);
			}
			// remove scripts
			foreach (iterator_to_array($elm->getElementsByTagName("script")) as $script) {
				$script->parentNode->removeChild($script);
			}
			// remove noscript
			foreach (iterator_to_array($elm->getElementsByTagName("noscript")) as $noscript) {
				$noscript->parentNode->removeChild($noscript);
			}
			
			$meta['body'] = trim(strip_tags($elm->nodeValue));
			$meta['body'] = preg_replace('/(?:(?:\r\n|\r|\n)\s*){2}/s', PHP_EOL, $meta['body']);
		}
		
		return $meta;
    }
    
    /**
     * Attempts to get screen shot of domain
     */
    public function get_http_screenshot($domain = '', $update = false)
    {
        if (empty($domain)) {
            return [
                'error' => 'Empty domain'    
            ];
        }
        
        if (!$this->validate($domain)) {
            return [
                'error' => 'Invalid domain'    
            ];
        }
        
        // switch into screenshots table
        $this->table = 'screenshot';
        
        // find or create the screenshot row
        $screenshot = $this->findOrCreate([
            'domain' => (string) $domain
        ]);

        // validate a change, update or return
        if (
            !$update && 
            !empty($screenshot->id) && 
            file_exists($screenshot->path) && 
            md5_file($screenshot->path) == $screenshot->checksum
        ) {
            return $screenshot;
        }

        // take screenshot
        try {
            $snappy = new \Knp\Snappy\Image('vendor/bin/wkhtmltoimage-amd64');

            // loop over and apply wkhtmltopdf options
            foreach ([
                'width' => '1024',
                'height' => '768',
                //'enable-plugins' => true,
                //'javascript-delay' => '1000',
                'enable-local-file-access' => false,
            ] as $key => $value
            ) {
                $snappy->setOption($key, $value);
            }

            $img = $snappy->getOutput('http://'.$domain);
        } catch (\Exception $e) {
            $img = [
                'error' => $e->getMessage()    
            ];
        }
        
        // save image
        $screenshot->name = 'screen_'.$domain;
        
        // failed to fetch/
        if (!empty($img['error'])) {
            // save image
            $screenshot->error = $img['error'];
        } else {
            $screenshot->ext  = 'jpg';
            $screenshot->path = './data/'.$screenshot->name.'.jpg';
            $screenshot->mime = 'image/jpg';
            $screenshot->checksum = md5($img);
            $screenshot->size = file_put_contents($screenshot->path, $img);
        }

        if ($update) {
            $screenshot->updated = date_create();
        } else {
            $screenshot->added = date_create();
        }

        // store and fresh
        $this->store($screenshot);
        //
        $screenshot = $screenshot->fresh();
        
        // switch back into domains table
        $this->table = 'domains';

        return $screenshot;

        /*
        header('Content-Type: image/jpg');
        header('Content-Length: '.strlen($pdf));
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Pragma: public');
        header('Expires: Thurs, 24 Mar 1983 00:00:00 GMT');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        die($pdf);
        */
    }

}
