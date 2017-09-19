<?php

namespace App\AdminModule\Presenters;

use Nette,
    App\Model;

use App\Model\Category;
use Nette\Database\Context;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    /** @var Model\Article @inject */
    public $articleRepo;

    private $secretArticles;

	public function actionDefault() {
		if (! $this->user->loggedIn) {
			$this->redirect('Sign:in');
		}
		if ($this->user->isAllowed('article','readSecret')) {
            $this->secretArticles = $this->articleRepo->createSelectionInstance()
                ->where(['secret' => TRUE])
                ->order('created_at DESC');
        } else {
		    $this->secretArticles = [];
        }

	}

	public function renderDefault()
	{
	    $this->template->allowedReadSecretArticles = $this->user->isAllowed('article','readSecret');
	    $this->template->allowedEditArticles = $this->user->isAllowed('article','edit');
        $this->template->secretArticles = $this->secretArticles;
	}

}
