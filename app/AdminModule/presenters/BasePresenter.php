<?php

namespace App\AdminModule\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	const SAVE_DIR = "../www/content/";
	const MAX_DIMENSION = 800;
	const EMAIL_OPERATOR = 'liskovamaj@gmail.com';

	public function beforeRender()
	{
		$this->template->loggedIn = $this->getUser()->isLoggedIn();
		$this->template->nuser = $this->user;

		if ($this->getUser()->isInRole('admin') && $this->user->getIdentity()) {
			$this->template->nuserEmail = $this->user->getIdentity()->data['email'];
		} else {
			$this->template->nuserEmail = '';
		}
		$user = $this->getUser();
		if ($this->name != 'Admin:Sign') {
			if (!$user->isLoggedIn()) {
				if ($user->getLogoutReason() === Nette\Security\User::INACTIVITY) {
					$this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostních důvodů odhlásil.', 'warning');
				}
				$this->redirect('Sign:in');
			} else {
				// Reseni acl, zatim staci jen prihlaseny uzivatel
				/*if (!$user->isAllowed($this->reflection->name, $this->getAction())) {
					$this->flashMessage('Na vstup do této sekce nemáte dostatečné oprávnění!', 'warning');
					$this->redirect('Sign:in');
				}*/

			}
		}
	}

	protected function getDatabase()
	{
		return $this->context->getService('database');
	}

	/**
	 * @param Nette\Http\FileUpload $file
	 * @param string $path
	 * @param array $enabledExt
	 * @return bool
	 */
	protected function saveFile($file, $path, $enabledExt = array('jpg', 'jpeg'))
	{
		if ($file->error) {
			return false;
		}

		$filename = $file->getName();

		if (!in_array(self::getExtensionByName($filename), $enabledExt)) {
			return false;
		}

		try {
			$src = imagecreatefromjpeg($file->getTemporaryFile());
			list($width, $height) = getimagesize($file->getTemporaryFile());

			$aspectRatio = $width / $height;

			if ($aspectRatio > 1) {
				$targetWidth = self::MAX_DIMENSION;
				$targetHeight = round(self::MAX_DIMENSION / $aspectRatio);
			} else {
				$targetWidth = round(self::MAX_DIMENSION * $aspectRatio);
				$targetHeight = self::MAX_DIMENSION;
			}

			$bigThumbnail = imagecreatetruecolor($targetWidth, $targetHeight);
			imagecopyresampled($bigThumbnail, $src, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);
			imagejpeg($bigThumbnail, self::SAVE_DIR . $path, 75);

		} catch (\Exception $e) {
			return false;
		}
		return true;
	}

	/**
	 * Get file extension from filename
	 * @param string $filename
	 * @return string
	 */
	public static function getExtensionByName($filename) {
		$tmp = explode('.', $filename);
		return strtolower(end($tmp));
	}

}
