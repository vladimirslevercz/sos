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
class MenuPresenter extends BasePresenter
{
	/**
	 * @var Model\Menu
	 * @inject
	 */
	public $menu;

	/**
	 * @var Model\Article
	 * @inject
	 */
	public $article;

	public function actionDefault() {
	}

	public function renderDefault()
	{
		$this->template->menu = $this->menu;
	}

	/**
	 * @param $id
	 */
	public function actionEdit($id) {
		$menu = $this->menu->get($id);
		if(!$menu) {
			$this->error('Data nebyla nalezena v databázi.', 404);
		}
		//dump($menu->toArray());exit;
		$defaults = $menu->toArray();
		if (!$defaults['menu_id']) {
			$defaults['menu_id'] = '';
		}
		$this['menuForm']->setDefaults($defaults);

	}

	protected function createComponentMenuForm()
	{
		$menus = $this->menu->where('menu_id', null)->fetchPairs('id', 'name');
		$menus[null] = '-- Není --';

		$articles = $this->article->fetchPairs('id', 'name');

		$articles[null] = '-- Není --';


		$form = new UI\Form;
		$form->addSelect('menu_id', 'Nadřazené menu', $menus);

		$form->addText('name', 'Název:')
			->setRequired();

		$form->addText('url', 'Url:');

		$form->addSelect('article_id', 'Článek:', $articles);
		$form->addSubmit('save', 'Uložit')
			->setAttribute('class', 'btn btn-primary');

		$form->onSuccess[] = array($this, 'menuFormSucceeded');

		return $form;
	}


	public function menuFormSucceeded(UI\Form $form, $values)
	{
		$this->menu = new Model\Menu($this->getDatabase());
		$menuId = $this->getParameter('id');
		if(!$values['menu_id']) {
			$values['menu_id'] = null;
		}
		if(!$values['article_id']) {
			$values['article_id'] = null;
		}
		if ($menuId) {
			$menu = $this->menu->get($menuId);
			if (!$menu) {
				$this->error('Data nebyla nalezena v databázi.', '404');
			} else {
				$menu->update($values);
			}
			$this->flashMessage('Změny uloženy.', 'success');

		} else {
			$this->menu->insert($values);
			$this->flashMessage('Položka menu "' . $values['name'] . '" vložena.', 'success');
		}
		$this->redirect('default');
	}

	public function actionDelete($id) {
		$this->menu->get($id)->delete();
		$this->flashMessage('Položka menu odstraněna odstraněn.', 'success');
		$this->redirect('default');
	}
}
