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
class HomepagePresenter extends BasePresenter
{
	/**
	 * @var Model\Event
	 * @inject
	 */
	public $event;

	public function renderDefault()
	{
		$this->template->event = $this->event->where('date > ?', new \DateTime('now'))->order('date ASC')->limit(2);
	}

}
