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
	public $menuRepo;

	/**
	 * @var Model\Article
	 * @inject
	 */
	public $articleRepo;

	private $article, $menu;

	public function renderDefault()
	{
	}

    public function actionArticle($id)
    {
        $this->article = $this->articleRepo->get($id);
        if (!$this->article) {
            $this->error('Článek nelze otevřít', 404);
        }

        if ($this->article->secret) {
            if (!$this->user->isLoggedIn() || !$this->user->isAllowed('article', 'readSecret')) {
                $this->flashMessage('Tento článek je tajný, je nutné se přihlásit.');
                $this->redirect(':Admin:Sign:in');
            }
        }

	}

	public function renderShow($id) {
		$this->menuRepo = new Model\Menu($this->getDatabase());
		$menuToOpen = $selectedMenu = $this->menuRepo->get($id);

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
		$this->template->selectedSubMenu = $selectedSubMenu;
	}

	public function renderArticle($id)
    {
        $this->template->article = $this->article;
	}

}
