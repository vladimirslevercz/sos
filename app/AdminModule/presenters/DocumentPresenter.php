<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;

use App\Model\Category;
use Nette\Database\Context;
use Nette\Application\UI;


/**
 * Document upload presenter.
 */
class DocumentPresenter extends BasePresenter
{
	const FILE_PATH = 'document/';

	/**
	 * @var Model\Document
	 * @inject
	 */
	public $documentRepo;

    public function startup()
    {
        parent::startup();
        if (!$this->user->isAllowed('document', 'show')) {
            $this->flashMessage('Na tuto akci nemáte oprávnění.');
            $this->redirect('Homepage:default');
        }
    }

	public function renderDefault()
	{
        $this->template->myDocuments = $this->documentRepo
            ->where('user_id = ?', $this->user->getId())
            ->order('id DESC');
        if ($this->user->isAllowed('document', 'showPrivate')) {
            $this->template->foreignDocuments = $this->documentRepo
                ->createSelectionInstance()
                ->where('user_id != ?', $this->user->getId())
                ->order('id DESC');
        } else {
            $this->template->foreignDocuments = $this->documentRepo
                ->createSelectionInstance()
                ->where('user_id != ?', $this->user->getId())
                ->where('private = ?', false)
                ->order('id DESC');
        }

    }

	/**
	 * @param $id
	 */
	public function actionEdit($id)
    {
        if (!$this->can($id, 'edit')) {
            $this->flashMessage('Na tuto akci nemáte dostatečná oprávnění.', 'warning');
            $this->redirect('default');
        }
        $document = $this->documentRepo->get($id);
		if(!$document) {
			$this->error('Data nebyla nalezena v databázi.', 404);
		}
		$this['renameForm']->setDefaults([
		    'id' => $document->id,
            'name' => $document->nice_name,
            'private' => $document->private,
        ]);
	}

	protected function createComponentDocumentForm()
	{
		$form = new UI\Form;

		$form->addUpload('document', 'Dokument')
			->addCondition($form::FILLED)
				->addRule($form::MAX_FILE_SIZE, 'Maximální velikost souboru je 10 MB.', 11 * 1024 * 1024 /* v bytech */);

        $form->addText('name', 'Vlastní název');

		$form->addCheckbox('private', 'Soukromý dokument:');

		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'btn btn-primary');

		$form->onSuccess[] = array($this, 'documentFormSucceeded');

		return $form;
	}

	public function documentFormSucceeded(UI\Form $form, $values)
	{
	    if ($this->user->isAllowed('document', 'create')) {
            $document = $this->documentRepo->saveFile($values['document'], $values['name'], $values['private']);
            $this->flashMessage('Dokument vložen do databáze.', 'success');
        } else {
            $this->flashMessage('Na tuto akci nemáte dostatečná oprávnění.', 'warning');
        }
		$this->redirect('default');
	}

	protected function createComponentRenameForm()
    {
        $form = new UI\Form;

        $form->addHidden('id');

        $form->addText('name', 'Vlastní název');

        $form->addCheckbox('private', 'Soukromý dokument:');

        $form->addSubmit('save', 'Uložit')
            ->setAttribute('class', 'btn btn-primary');

        $form->onSuccess[] = array($this, 'renameFormSucceeded');

        return $form;
    }

    public function renameFormSucceeded(UI\Form $form, $values)
    {
        if ($this->can($values['id'], 'edit')) {
            $document = $this->documentRepo->get($values['id']);
            if (!$document) {
                $this->error('Data nebyla nalezena v databázi.', '404');
            } else {
                $document->update([
                    'nice_name' => $values['name'],
                    'private' => $values['private'],
                ]);
            }
            $this->flashMessage('Změny uloženy.', 'success');
        } else {
            $this->flashMessage('Na tuto akci nemáte oprávnění.', 'warning');
        }
        $this->redirect('default');
    }

	public function actionDelete($id)
    {
        if ($this->can($id, 'delete')) {
            $document = $this->documentRepo->get($id);
            $this->documentRepo->deleteFile($document);
            $this->documentRepo->delete();
            $this->flashMessage('Dokument odstraněn.', 'success');
        } else {
            $this->flashMessage('Na tuto akci nemáte oprávnění.', 'warning');
        }
		$this->redirect('default');
	}

    private function can($id, $action = 'edit')
    {
        $document = $this->documentRepo->get($id);
        return $this->user->isAllowed('document', $action) || $document->user_id == $this->user->getId();
	}

    public function actionDownload($id)
    {
        $document = $this->documentRepo->get($id);
        if (!$document) {
            $this->flashMessage('Soubor neexistuje','danger');
            $this->redirect('default');
        }

        if (!$this->can($id, 'show')) {
            $this->flashMessage('Na tuto akci nemáte oprávnění.', 'warning');
            $this->redirect('default');
        }

        if ($document->private && !$this->can($id, 'showPrivate')) {
            $this->flashMessage('Na tuto akci nemáte oprávnění.', 'warning');
            $this->redirect('default');
        }

        $filepath = Model\Document::SAVE_DIR . $document->path;

        $fileDownloadName = Nette\Utils\Strings::webalize(pathinfo($document->nice_name)['filename']).'.'
            .pathinfo($document->nice_name)['extension'];

        if(!file_exists($filepath)) {
            $this->flashMessage('Dokument nelze načíct, soubor nebyl nalezen.','danger');
            $this->redirect('default');
        }
        header('Content-Transfer-Encoding: binary');  // For Gecko browsers mainly
        header('Last-Modified: ' . $document->created_at . ' GMT');
        header('Accept-Ranges: bytes');  // Allow support for download resume
        header('Content-Length: ' . filesize($filepath));  // File size
        header('Content-Encoding: none');
        header('Content-Type: ' . mime_content_type($filepath));  // Change the mime type if the file is not PDF
        header('Content-Disposition: attachment; filename=' . $fileDownloadName);  // Make the browser display the Save As dialog
        readfile($filepath);  // This is necessary in order to get it to actually download the file, otherwise it will be 0Kb
        exit();
    }
}
