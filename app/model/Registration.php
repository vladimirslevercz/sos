<?php

namespace App\Model;

/**
 * Persistent object Registration.
 */
class Registration extends \Nette\Database\Table\Selection {
	private $db;
	private $table = "registration";

	public function __construct(\Nette\Database\Context $database) {
		parent::__construct($database, $database->getConventions(), $this->table);
		$this->db = $database;
	}

}
