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

	/**
	 * @var Model\Ticket
	 * @inject
	 */
	public $ticket;

	/**
	 * @var Model\Registration
	 * @inject
	 */
	public $registration;


	public function renderShow($id) {
		$event = $this->event->get($id);
		if (!$event) {
			$this->error('Akci nelze otevřít', 404);
		}

		$this['eventRegistrationForm']->setDefaults(['event_id' => $id]);
		$this['eventTicketForm']->setDefaults(['event_id' => $id]);

		$this->template->event = $event;
		$this->template->now = new \DateTime('now');
		$this->template->ticket = $event->related('ticket');
		$this->template->registration = $event->related('registration');
	}

	public function renderDefault($filter = 'all') {
		$eventMain = clone $this->event;
		$eventNotMain = clone $this->event;
		$eventPast = clone $this->event;
		$this->template->eventFuture = $eventMain->where('date > ?', new \DateTime('+3months'));
		$this->template->eventNow = $eventNotMain->where('date > ?', new \DateTime('now'))->where('date < ?', new \DateTime('+3months'))->order('date ASC');
		$this->template->eventPast = $eventPast->where('date < ?', new \DateTime('now'))->limit(15);
		$this->template->filter = $filter;
	}

	protected function createComponentEventRegistrationForm()
	{
		$form = new Nette\Application\UI\Form();

		//$form->getElementPrototype()->class = 'form-horizontal';

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
		$event = $this->event->get($values['event_id']);
		$duplicite = $this->registration->where('event_id = ? AND email = ?', [$values['event_id'], $values['email']]);
		if (count($duplicite) >= $event['max_ticket_per_email']) {
			$this->flashMessage('Byla nalezena starší registrace, nebyly provedeny žádné změny. Změny konzultujte s pořadatelkou.', 'warning');
			$this->redirect('this');
		}

		try {
			$res = $this->registration->insert($values);
		} catch (\Exception $e) {
			$res = false;
		}

		
		if ($res) {
			$this->mailNotify('SOS - nova registrace', 'Probehla registrace '. $values['name'] .' '. $values['surname'] .' k akci '. $event->name .'.', $values);
			$this->flashMessage('Registrace proběhla v pořádku.', 'success');
		} else {
			$this->flashMessage('Je nám líto, ale došlo k chybě. S registrací se obraťte se pořadatelku akce.', 'danger');
		}

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

		$form->addSubmit('send', 'Získat vstupenku')
			->setAttribute('class', 'btn btn-primary');

		$form->onSuccess[] = array($this, 'eventTicketFormSucceeded');

		return $form;
	}

	public function eventTicketFormSucceeded(Nette\Application\UI\Form $form, $values)
	{
		// Udalost
		$event = $this->event->get($values['event_id']);

		// Drive zamluvene vstupenky
		$duplicite = $this->ticket->where('event_id = ? AND email = ?', [$values['event_id'], $values['email']]);

		// Vycerpan limit
		if (count($duplicite) >= $event['max_ticket_per_email']) {
			$this->flashMessage('Limit na počet vstupenek pro Váš email byl již vyčerpán, použijte jiný email.', 'warning');
			$this->redirect('this');
		}

		// Ulozeni do databaze
		try {
			$ticket = $this->ticket->insert($values);
		} catch (\Exception $e) {
			$ticket = false;
		}

		// Notifikace na email a odeslání vstupenky
		if ($ticket) {
			$this->mailNotify('SOS - nova vstupenka', 'Uzivatel s emailem '. $values['email'] .' si zazadal o vstupenku na akci '. $event->name .'.', $values);
			$this->sendTicket($ticket);

			$this->flashMessage('Byla Vám zaslána vstupenka.', 'success');
		} else {
			$this->flashMessage('Je nám líto, ale došlo k chybě. Pro vstupenku se obraťte se pořadatelku akce.', 'danger');
		}

		$this->redirect('this');
	}

	private function sendTicket($ticket)
	{
		$event = $ticket->event;
		$message = "<p>Děkujeme o Váš zájem, zde je Vaše vstupenka, můžete si ji vytisknout nebo připravit do mobilního telefonu.</p>\n"
			."<img src=\"http://hudebnisos.cz/content/ticket/". $event->id .".jpg\" width=\"600\" />\n"
			."<p>Vaše vstupenka má originální číslo <b>". $ticket->id ."</b>.</p><br/><br/>\n"
			."<p><a href=\"http://hudebnisos.cz/event/show/". $event->id ."\">odkaz na akci ".$event->name."</p>\n"
			."<p><a href=\"http://hudebnisos.cz/\">www.hudebnisos.cz</a></p>\n";

		$mail = new Nette\Mail\Message();
		$mail->setSubject('SOS Vstupenka - '.$event->name);
		$mail->setHtmlBody($message);
		$mail->addTo($ticket->email);
		$mailer = new Nette\Mail\SendmailMailer();
		$mailer->send($mail);
	}
}
