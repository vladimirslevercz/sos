<?php

namespace App\FrontModule\Presenters;

use Nette,
	App\Model;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter
{
	const NOTIFY_EMAIL_ADDRESS = 'mlazovla@gmail.com';

	/**
	 * @var Model\Menu
	 * @inject
	 */
	public $menu;

	public function beforeRender()
	{
		$this->template->menus = $this->menu->where('menu_id', null);
	}

	protected function getDatabase() {
		return $this->context->getService('database');
	}

	/**
	 * Notify an humean on email
	 *
	 * @param $subject
	 * @param $message
	 * @param array $data
	 */
	public function mailNotify($subject, $message, $data = array())
	{
		$table = "<table>\n";
		foreach($data as $key => $value) {
			try {
				$value = (string)$value;
			} catch (\Exception $e){
				$value = "<i>hodnota není text</i>";
			}
			$table .= "<tr><th>$key</th><td>$value</td></tr>\n";

		}
		$table .= "</table>\n";
		$message = "<p>$message</p>\n\n";

		if(count($data)) {
			$message .= "<p>Tabulka dat:</p>\n$table";
		}

		$mail = new Nette\Mail\Message();
		$mail->setSubject($subject);
		$mail->setHtmlBody($message);
		$mail->addTo(self::NOTIFY_EMAIL_ADDRESS);
		$mailer = new Nette\Mail\SendmailMailer();
		$mailer->send($mail);
	}
}
