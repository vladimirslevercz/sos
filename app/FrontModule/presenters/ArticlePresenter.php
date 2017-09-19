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

	private $article, $selectedMenu, $selectedSubMenu;

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

    public function actionShow($id)
    {
        $menuToOpen = $this->menuRepo->get($id);
        $this->selectedMenu = $menuToOpen;

        $selectedSubMenu = null;
        if ($this->selectedMenu->menu) {
            $this->selectedSubMenu = $this->selectedMenu;
            $this->selectedMenu = $this->selectedMenu->menu;
        }

        if (!$this->selectedMenu) {
            $this->redirect('Homepage:default');
        }

        $this->article = $menuToOpen->article;
        if (!$this->article) {
            $this->error('Menu nelze otevřít', 404);
        }
        if ($this->article->secret) {
            if (!$this->user->isLoggedIn() || !$this->user->isAllowed('article', 'readSecret')) {
                $this->flashMessage('Tento článek je tajný, je nutné se přihlásit.');
                $this->redirect(':Admin:Sign:in');
            }
        }

	}

	public function renderShow($id) {

		$this->template->article = $this->article;
		$this->template->selectedMenu = $this->selectedMenu;
		$this->template->selectedSubMenu = $this->selectedSubMenu;
	}

	public function renderArticle($id)
    {
        $this->template->article = $this->article;
	}

}
