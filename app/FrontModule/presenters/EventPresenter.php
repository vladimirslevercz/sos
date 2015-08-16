<?php

namespace App\FrontModule\Presenters;

use Nette,
    App\Model;

use App\Model\Article;
use App\Model\Category;
use Nette\Database\Context;

/**
 * Homepage presenter.
 */
class EventPresenter extends BasePresenter
{
	/**
	 * @var Model\Event
	 * @inject
	 */
	public $event;


	public function renderShow($id) {
		$event = $this->event->get($id);
		if (!$event) {
			$this->error('Akci nelze otevřít', 404);
		}
		$this->template->event = $event;
	}

	public function renderDefault() {
		$eventMain = clone $this->event;
		$eventNotMain = clone $this->event;
		$eventPast = clone $this->event;
		$this->template->eventMain = $eventMain->where('main = ?', 1)->where('date > ?', new \DateTime('now'));
		$this->template->eventNotMain = $eventNotMain->where('main != ?', 1)->where('date > ?', new \DateTime('now'));
		$this->template->eventPast = $eventPast->where('date < ?', new \DateTime('now'))->limit(15);

	}
}
