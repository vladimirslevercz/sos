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
		$this->template->now = new \DateTime('now');
	}

	public function renderDefault() {
		$eventMain = clone $this->event;
		$eventNotMain = clone $this->event;
		$eventPast = clone $this->event;
		$this->template->eventMain = $eventMain->where('main = ?', 1)->where('date > ?', new \DateTime('now'));
		$this->template->eventNotMain = $eventNotMain->where('main != ?', 1)->where('date > ?', new \DateTime('now'));
		$this->template->eventPast = $eventPast->where('date < ?', new \DateTime('now'))->limit(15);
	}

	protected function createComponentEventRegistrationForm()
	{
		$form = new Nette\Application\UI\Form();

		$form->addText('name', 'Jméno')
			->setRequired('Vyplňte prosím jméno')
			->setAttribute('class', 'input-sm');

		$form->addText('surname', 'Příjmení')
			->setRequired('Vyplňte prosím příjmení')
			->setAttribute('class', 'input-sm');

		$form->addText('birthdate', 'Datum narození')
			->setType('date')
			->setRequired('Vyplňte prosím datum narození')
			->setAttribute('class', 'input-sm');

		$form->addText('phone',  'Mobilní telefon')
			->setType('tel')
			->setAttribute('class', 'input-sm');

		$form->addText('email', 'Email')
			->setType('email')
			->addRule($form::EMAIL, 'Zkontrolujte si tvar emailové adresy')
			->setRequired('Vyplňte prosím email')
			->setAttribute('class', 'input-sm');

		$form->addTextArea('note', 'Poznámka')
			->setAttribute('class', 'input-sm');

		$form->addHidden('event_id');

		$form->addSubmit('send', 'Registrovat')
			->setAttribute('class', 'btn btn-primary');

		$form->onSuccess[] = array($this, 'eventRegistrationFormSucceeded');

		return $form;
	}

	public function eventRegistrationFormSucceeded(Nette\Application\UI\Form $form, $values)
	{
		$this->flashMessage('Byl jste registrován.');
		$this->redirect('this');
	}

	protected function createComponentEventTicketForm()
	{
		$form = new Nette\Application\UI\Form();

		$form->addText('email', 'Email')
			->setType('email')
			->addRule($form::EMAIL, 'Zkontrolujte si tvar emailové adresy')
			->setRequired('Vyplňte prosím email')
			->setAttribute('class', 'input-sm');

		$form->addHidden('event_id');

		$form->addSubmit('send', 'Získat lístek')
			->setAttribute('class', 'btn btn-primary');

		$form->onSuccess[] = array($this, 'eventTicketFormSucceeded');

		return $form;
	}

	public function eventTicketFormSucceeded(Nette\Application\UI\Form $form, $values)
	{
		$this->flashMessage('Byla Vám zaslána vstupenka.', 'success');
		$this->redirect('this');
	}

}
