<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;

use App\Model\Category;
use Nette\Database\Context;
use Nette\Application\UI;


/**
 * Ticket edit presenter.
 */
class TicketPresenter extends BasePresenter
{

	/**
	 * @var Model\Ticket
	 * @inject
	 */
	public $ticket;


	public function renderDefault($event_id = null)
	{
		if ($event_id) {
			$this->template->ticket = $this->ticket->where('event_id = ?', $event_id)->order('id DESC');
		} else {
			$this->template->ticket = $this->ticket->order('id DESC')->limit(100);
		}
	}

	public function actionDelete($id) {
		$this->ticket->get($id)->delete();
		$this->flashMessage('Vstupenka odstraněna.', 'success');
		$this->redirect('default');
	}
}
