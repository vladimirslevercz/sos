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

	public function renderDefault()
	{

	}

	public function renderShow($id) {
		$this->menu = new Model\Menu($this->getDatabase());
		$selectedMenu = $this->menu->get($id);
		$selectedSubMenu = null;
		if ($selectedMenu->menu) {
			$selectedSubMenu = $selectedMenu;
			$selectedMenu = $selectedMenu->menu;
		}

		if (!$selectedMenu) {
			$this->redirect('Homepage:default');
		}
		$this->template->article = $selectedMenu->article;
		$this->template->selectedMenu = $selectedMenu;
		$this->template->selectedSubmenu = $selectedSubMenu;
	}

}
