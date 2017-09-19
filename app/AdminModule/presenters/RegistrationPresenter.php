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

	/**
	 * @var Model\Event
	 * @inject
	 */
	public $event;

    public function startup()
    {
        parent::startup();
        if (!$this->user->isAllowed('registration', 'show')) {
            $this->flashMessage('Na tuto akci nemáte oprávnění.');
            $this->redirect('Homepage:default');
        }
    }

	public function renderDefault($event_id = null)
	{
		if ($event_id) {
			$this->template->filterName = $this->event->get($event_id)->name;
			$this->template->registration = $this->registration->where('event_id = ?', $event_id)->order('id DESC');
		} else {
			$this->template->filterName = '';
			$this->template->registration = $this->registration->order('id DESC')->limit(100);
		}
	}

	public function actionDelete($id) {
		$this->registration->get($id)->delete();
		$this->flashMessage('Registrace odstraněna.', 'success');
		$this->redirect('default');
	}
}
