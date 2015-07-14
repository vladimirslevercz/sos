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
    
    /**
     * @var Category
     * @inject
     */    
    public $category;    
        
	public function renderDefault()
	{

	}

}
