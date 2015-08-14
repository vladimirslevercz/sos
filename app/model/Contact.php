<?php

namespace App\Model;

/**
 * Persistent object Contact.
 */
class Contact extends \Nette\Database\Table\Selection {
	private $db;
	private $table = "contact";

	public function __construct(\Nette\Database\Context $database) {
		parent::__construct($database, $database->getConventions(), $this->table);
		$this->db = $database;
	}

}
