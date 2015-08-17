<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;

use App\Model\Category;
use Nette\Database\Context;
use Nette\Application\UI;


/**
 * Article edit presenter.
 */
class ArticlePresenter extends BasePresenter
{
	const FILE_PATH = 'article/';

	/**
	 * @var Model\Article
	 * @inject
	 */
	public $article;


	public function renderDefault()
	{
		$this->template->article = $this->article->order('id DESC');
	}

	/**
	 * @param $id
	 */
	public function actionEdit($id) {
		$article = $this->article->get($id);
		if(!$article) {
			$this->error('Data nebyla nalezena v databázi.', 404);
		}
		$this['articleForm']->setDefaults($article->toArray());
	}

	protected function createComponentArticleForm()
	{
		$form = new UI\Form;

		$form->addText('name', 'Název článku:')
			->setRequired();

		$form->addUpload('image', 'Obrázek JPG')
			->addCondition($form::FILLED)
				->addRule($form::IMAGE, 'Zvolený soubor není obrázek.')
				->addRule($form::MAX_FILE_SIZE, 'Maximální velikost souboru je 2 MB.', 3 * 1024 * 1024 /* v bytech */);

		$form->addTextArea('annotation', 'Annotace:')
			->setAttribute('class', 'tinyMCE');

		$form->addTextArea('text', 'Článek:')
			->setAttribute('class', 'tinyMCE');

		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'btn btn-primary');

		$form->onSuccess[] = array($this, 'articleFormSucceeded');

		return $form;
	}


	public function articleFormSucceeded(UI\Form $form, $values)
	{
		$articleData = [
			'name' => $values['name'],
			'user_id' => $this->user->id,
			'annotation' => $values['annotation'],
			'text' => $values['text'],
		];

		$articleId = $this->getParameter('id');
		if ($articleId) {
			$article = $this->article->get($articleId);
			if (!$article) {
				$this->error('Data nebyla nalezena v databázi.', '404');
			} else {
				$article->update($articleData);
			}
			$this->flashMessage('Změny uloženy.', 'success');

		} else {
			$article = $this->article->insert($articleData);
			$this->flashMessage('Článek vložen do databáze.', 'success');
		}

		if ($this->saveFile($values['image'], self::FILE_PATH . $article->id . '.jpg')) {
			$this->flashMessage('Obrázek uložen.', 'success');
		}

		$this->redirect('edit', $article->id);
	}

	public function actionDelete($id) {
		$this->article->get($id)->delete();
		$this->flashMessage('Článek odstraněn.', 'success');
		$this->redirect('default');
	}
}
