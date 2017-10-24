<?php
namespace Module\Submit;

class Controller extends \Framework\Controller
{
	/**
     *
     */
	public function __construct()
	{
		parent::__construct();

		$this->audit = \Audit::instance();

		// load models
		$this->domains = new \Model\Domains();
	}

	/**
     *
     */
	public function index(\Base $f3, $params)
	{
		$form = [
			'domains' => [],
			'errors'  => [],
			'values'  => !empty($f3->get('POST')) ? $f3->get('POST') : []
		];

		if (!empty($f3->get('POST'))) {
			// check csrf
			if (!$this->check_csrf($f3->get('POST.csrf'), false)) {
				$form['errors']['global'] = 'Invalid CSRF token, please try again.';
			}
			unset($form['values']['csrf']);

			// input both empty
			if (empty($form['values']['single']) && empty($form['values']['multi'])) {
				$form['errors']['global'] = 'Fill in either single domain or multi domain field.';
			}

			// input both set
			if (!empty($form['values']['single']) && !empty($form['values']['multi'])) {
				$form['errors']['global'] = 'Fill in either single domain or multi domain field.';
			}

			// SINGLE
			if (!empty($form['values']['single']) && empty($form['values']['multi'])) {
				// check single
				if (!empty($form['values']['single'])) {
					$form['domains'][] = $form['values']['single'];
				}
			}

			// MULTI
			if (empty($form['values']['single']) && !empty($form['values']['multi'])) {
				// split intput by line (filters empty)
				$form['domains'] = $this->domains->split(trim($form['values']['multi']));

				// input empty/whitespace
				if (empty($form['domains'])) {
					$form['errors']['global'] = 'You must enter at least one domain name.';
				}
			}
			
			if (!empty($form['domains']) && count($form['domains']) > 3000) {
				shuffle($form['domains']);
				$form['domains'] = array_unique($form['domains']);
				$form['domains'] = array_slice($form['domains'], 0, 3000);
				$_SESSION['errors']['global'] = 'You may only submit upto 3000 domains at once.';
			}

			// set into session for processing
			$_SESSION['domains'] = array_unique($form['domains']);
			$_SESSION['process'] = hash('sha256', json_encode($_SESSION['domains']));

			// all good goto processing
			if (!empty($form['domains'])) {
				$f3->reroute('/process/'.$_SESSION['process']);
			}
		}

		$this->set_csrf(false);
		$f3->set('form', $form);

		//
		$f3->mset([
			'template' => 'app/template/default/template.php',
			'page' => [
				'title' => 'Submit',
				'body' => $this->view->render('app/module/submit/view/index.php')
			]
		]);
	}
}
