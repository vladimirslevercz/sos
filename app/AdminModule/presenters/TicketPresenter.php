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

	/**
	 * @var Model\Event
	 * @inject
	 */
	public $event;

    public function startup()
    {
        parent::startup();
        if (!$this->user->isAllowed('ticket', 'edit')) {
            $this->flashMessage('Na tuto akci nemáte oprávnění.');
            $this->redirect('Homepage:default');
        }
    }

	public function renderDefault($event_id = null)
	{
		if ($event_id) {
			$this->template->filterName = $this->event->get($event_id)->name;
			$this->template->ticket = $this->ticket->where('event_id = ?', $event_id)->order('id DESC');
		} else {
			$this->template->filterName = '';
			$this->template->ticket = $this->ticket->order('id DESC')->limit(100);
		}
	}

	public function actionDelete($id) {
		$this->ticket->get($id)->delete();
		$this->flashMessage('Vstupenka odstraněna.', 'success');
		$this->redirect('default');
	}
}
