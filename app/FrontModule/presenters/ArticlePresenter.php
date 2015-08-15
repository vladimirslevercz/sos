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
class ArticlePresenter extends BasePresenter
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

	public function renderDefault()
	{

	}

	public function renderShow($id) {
		$this->menu = new Model\Menu($this->getDatabase());
		$menuToOpen = $selectedMenu = $this->menu->get($id);

		$selectedSubMenu = null;
		if ($selectedMenu->menu) {
			$selectedSubMenu = $selectedMenu;
			$selectedMenu = $selectedMenu->menu;
		}

		if (!$selectedMenu) {
			$this->redirect('Homepage:default');
		}


		$article = $menuToOpen->article;
		if (!$article) {
			$this->error('Menu nelze otevřít', 404);
		}

		$this->template->article = $article;
		$this->template->selectedMenu = $selectedMenu;
		$this->template->selectedSubmenu = $selectedSubMenu;
	}

	public function renderArticle($id) {
		$article = $this->article->get($id);
		if (!$article) {
			$this->error('Článek nelze otevřít', 404);
		}
		$this->template->article = $article;
	}

}
