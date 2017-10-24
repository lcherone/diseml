<?php
namespace Module\Index;

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
	    if (isset($_SESSION['domains']['unprocessed'])) {
	    	unset($_SESSION['domains']['unprocessed']);
	    }
	    
        //
        $f3->mset([
            'template' => 'app/template/default/template.php',
            'page' => [
                'title' => 'Example Module',
                'body' => $this->view->render('app/module/index/view/index.php')
            ]
        ]);
	}

}