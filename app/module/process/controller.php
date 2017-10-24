<?php
namespace Module\Process;

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
		$form = [];
		
		// chack got domains to process
		if (empty($_SESSION['domains']) || !is_array($_SESSION['domains'])) {
			$f3->reroute('/submit');
		}
		
		$form['domains'] = $_SESSION['domains'];
		unset($_SESSION['domains']);

		// check got domains and processing key
		if (empty($_SESSION['process']) || $_SESSION['process'] !== hash('sha256', json_encode($form['domains']))) {
			$f3->reroute('/submit');
		}

		//
		$this->set_csrf(false);
		
		//
		$f3->set('form', $form);
		
		// set path form menu link
		$f3->set('PATH', '/submit');

		//
		$f3->mset([
			'template' => 'app/template/default/template.php',
			'page' => [
				'title' => 'Process',
				'body' => $this->view->render('app/module/process/view/index.php')
			]
		]);
	}

}