<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;

use App\Model\Category;
use Nette\Database\Context;
use Nette\Application\UI;


/**
 * event edit presenter.
 */
class EventPresenter extends BasePresenter
{
	/**
	 * @var Model\Event
	 * @inject
	 */
	public $event;

	public function renderDefault()
	{
		$this->template->event = $this->event;
	}

	/**
	 * @param $id
	 */
	public function actionEdit($id) {
		$event = $this->event->get($id);
		if(!$event) {
			$this->error('Data nebyla nalezena v databázi.', 404);
		}
		$this['eventForm']->setDefaults($event->toArray());
	}

	protected function createComponentEventForm()
	{
		$form = new UI\Form;

		$form->addText('name', 'Název události:')
			->setRequired();

		$form->addUpload('eventImage', 'Obrázek akce')
			->addCondition($form::FILLED)
				->addRule($form::IMAGE, 'Zvolený soubor není obrázek.')
				->addRule($form::MAX_FILE_SIZE, 'Maximální velikost souboru je 2 MB.', 10 * 1024 * 1024 /* v bytech */);


		$form->addText('date', 'Datum a čas začátku události')
			->addRule($form::PATTERN, 'Vyplňte datum a čas ve tvaru 13.8.2014 18:00.', '([0-3]?[0-9]\.[0-1]?[0-9]\.[0-9]{4}( [0-2]?[0-9]:[0-5][0-9](:[0-5][0-9])?)?)|([0-9]{4}-[0-9]{2}-[0-9]{2}( [0-9]{2}:[0-9]{2}:[0-9]{2})?)')
			->setRequired();

		$form->addTextArea('annotation', 'Annotace:')
			->setAttribute('class', 'tinyMCE');

		$form->addTextArea('description', 'Článek:')
			->setAttribute('class', 'tinyMCE');

		$form->addCheckbox('main', 'Hlavní akce?');

		$form->addCheckbox('registration', 'Konference?');

		$form->addCheckbox('ticket', 'Koncert?')
			->addCondition($form::EQUAL, true)
			->toggle('ticket-image');

		$form->addCheckbox('document', 'Dokument ke stažení?')
			->addCondition($form::EQUAL, true)
			->toggle('document-to-download');


		$form->addUpload('attachedDocument', 'Dokument PDF')
			->setOption('id', 'document-to-download')
			->addRule($form::MAX_FILE_SIZE, 'Maximální velikost souboru je 6 MB.', 9 * 1024 * 1024 /* v bytech */);

		$form->addUpload('ticketImage', 'Obrázek vstupenky JPG')
			->setOption('id', 'ticket-image')
			->addCondition($form::FILLED)
			->addRule($form::IMAGE, 'Zvolený soubor není obrázek.')
			->addRule($form::MAX_FILE_SIZE, 'Maximální velikost souboru je 2 MB.', 10 * 1024 * 1024 /* v bytech */);

		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'btn btn-primary');

		$form->onValidate[] = array($this, 'formValidate');
		$form->onSuccess[] = array($this, 'formSucceeded');

		return $form;
	}

	public function formValidate(UI\Form $form, $values) {
		try {
			$date = new \DateTime($values['date']);
		} catch (\Exception $e) {
			$form->addError('Neplatný formát data.');
			return;
		}
	}

	public function formSucceeded(UI\Form $form, $values)
	{

		$eventData = [
			'name' => $values['name'],
			'annotation' => $values['annotation'],
			'description' => $values['description'],
			'date' => new \DateTime($values['date']),
			'main' => $values['main'],
			'ticket' => $values['ticket'],
			'registration' => $values['registration'],
			'document' => $values['document']
		];

		$eventId = $this->getParameter('id');
		if ($eventId) {
			$event = $this->event->get($eventId);
			if (!$event) {
				$this->error('Data nebyla nalezena v databázi.', '404');
			} else {
				$event->update($eventData);
			}
			$this->flashMessage('Změny uloženy.', 'success');

		} else {
			$event = $this->event->insert($eventData);
			$this->flashMessage('Akce vložena do databáze.', 'success');
		}

		// obrázky
		if ($this->saveFile($values['eventImage'], 'event/' . $event->id . '.jpg')) {
			$this->flashMessage('Obrázek akce uložen.', 'success');
		}
		if ($this->saveFile($values['ticketImage'], 'ticket/' . $event->id . '.jpg')) {
			$this->flashMessage('Obrázek vstupenky uložen.', 'success');
		}

		// PDFko
		/** @var Nette\Http\FileUpload $attachedDocument */
		$attachedDocument = $values['attachedDocument'];
		if ($attachedDocument->isOk() || self::getExtensionByName($attachedDocument->getName()) == 'pdf') {
			$attachedDocument->move(self::SAVE_DIR . '/event-pdf/' . $event->id . '.pdf');
			$this->flashMessage('Dokument uložen.', 'success');
		}

		$this->redirect('edit', $event->id);
	}


	public function actionDelete($id)
	{
		$this->event->get($id)->delete();
		// TODO REMOVE FILES
		$this->flashMessage('Článek odstraněn.', 'success');
		$this->redirect('default');
	}

	public function handleToggleMain($id)
	{
		$event = $this->event->get($id);
		if (!$event) {
			$this->flashMessage('Akce nenalezena.', 'danger');
		} else {
			$event->update(['main' => !$event->main]);
			$this->flashMessage('Akce upravena.', 'success');
		}
		$this->redirect('default');

	}
}
