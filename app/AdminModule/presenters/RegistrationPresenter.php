<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;

use App\Model\Category;
use Nette\Database\Context;
use Nette\Application\UI;


/**
 * Registration edit presenter.
 */
class RegistrationPresenter extends BasePresenter
{

	/**
	 * @var Model\Registration
	 * @inject
	 */
	public $registration;


	public function renderDefault($event_id = null)
	{
		if ($event_id) {
			$this->template->registration = $this->registration->where('event_id = ?', $event_id)->order('id DESC');
		} else {
			$this->template->registration = $this->registration->order('id DESC')->limit(100);
		}
	}

	public function actionDelete($id) {
		$this->registration->get($id)->delete();
		$this->flashMessage('Registrace odstraněna.', 'success');
		$this->redirect('default');
	}
}