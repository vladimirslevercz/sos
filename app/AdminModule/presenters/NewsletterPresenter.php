<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;

use App\Model\Category;
use Nette\Database\Context;
use Nette\Application\UI;


/**
 * Menu edit presenter.
 */
class NewsletterPresenter extends BasePresenter
{
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

	/**
	 * @var Model\Event
	 * @inject
	 */
	public $event;

	public function actionDefault() {
	}

	public function renderDefault()
	{

	}

	protected function createComponentNewsletterForm()
	{
		$events = $this->event->order('id DESC')->limit(10)->fetchPairs('id', 'name');
		$events[null] = 'ODESLAT VŠEM';

		$form = new UI\Form;
		$form->addSelect('event_id', 'Účatníků události', $events);

		$form->addText('subject', 'Předmět:');

		$form->addTextArea('text', 'text')
			->setAttribute('class', 'tinyMCE');

		$form->addCheckbox('notTest', 'TOTO NENÍ TEST');

		$form->addMultiUpload('files', 'Přílohy');

		$form->addSubmit('send', 'Odeslat')
			->setAttribute('class', 'btn btn-danger');

		$form->onSuccess[] = array($this, 'newsletterFormSucceeded');

		$form->setDefaults($this->loadFormValues());

		return $form;
	}


	public function newsletterFormSucceeded(UI\Form $form, $values)
	{
		if(!$values['event_id']) {
			$values['event_id'] = null;
		}

		$this->saveFormValues($values);

		$contacts = array();
		if (!$values['event_id'] && $values['notTest']) {
			foreach($this->registration as $registration) {
				$contacts[] = $registration->email;
			}
			foreach($this->ticket as $ticket) {
				$contacts[] = $ticket->email;
			}
			$contacts = array_unique($contacts);
		} elseif ($values['notTest']) {
			foreach($this->registration->where('event_id = ?', $values['event_id']) as $registration) {
				$contacts[] = $registration->email;
			}
			foreach($this->ticket->where('event_id = ?', $values['event_id']) as $ticket) {
				$contacts[] = $ticket->email;
			}
			$contacts = array_unique($contacts);
		} else {
			$contacts = [0 => self::EMAIL_OPERATOR];
		}

		$mail = new Nette\Mail\Message();

		$mail->setFrom('Hudební S.O.S. <info@hudebnisos.cz>')
			->setSubject($values['subject'])
			->setHtmlBody('
				<html><head><title>'.$values['subject'].'</title></head>
				<body>
				<div style="background-color: rgb(197, 230, 235);color: rgb(0, 117, 117);letter-spacing: 0.3px; margin: auto auto; padding:1em 1em">
				'.$values['text'].'
				<br /><br />
				</div>
				<hr style="border:0;border-top: 1px solid #DDD;" />
				<center>
				<small>
				<font style="color:silver;font-family:\'Helvetica Neue\', sans-sherif">Pro odhlášení odpovězte na tento email slovy "Prosím odhlásit".</font>
				</small>
				</center>
				</body></html>');

		/** @var Nette\Http\FileUpload $attach */
		foreach($values['files'] as $attach) {
			$mail->addAttachment($attach->getSanitizedName(), $attach->getContents());
		}

		$counter = 0;
		foreach ($contacts as $contact) {
			$counter++;
			$mail->addTo($contact);
		}

		$mailer = new Nette\Mail\SendmailMailer();
		try {
			$mailer->send($mail);

			if (!$values['notTest']) {
				$this->flashMessage("Byl odeslán testovací email.", 'success');
			} else {
				$this->flashMessage("Bylo odesláno $counter emailů.", 'success');
			}
		} catch (\Exception $e) {
			$this->flashMessage('Při odesílání došlo k chybě. :: '.$e->getMessage());
		}

		$this->redirect('this');
	}

	private function saveFormValues($values)
	{
		$sessionSection = $this->getSession()->getSection('newsletterForm');
		$sessionSection->setExpiration("60 minutes");
		foreach ($values as $k => $v) {
			if (is_string($v)) {
				$sessionSection[$k] = $v;
			}
		}
	}

	private function loadFormValues()
	{
		$res = array();
		$this->getSession()->getSection('newsletterForm');
		foreach($this->getSession()->getSection('newsletterForm') as $k => $v) {
			$res[$k] = $v;
		}
		return $res;
	}

}
