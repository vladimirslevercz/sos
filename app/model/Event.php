<?php

namespace App\Model;

/**
 * Persistent object Event.
 */
class Event extends \Nette\Database\Table\Selection {
	private $db;
	private $table = "event";

	public function __construct(\Nette\Database\Context $database) {
		parent::__construct($database, $database->getConventions(), $this->table);
		$this->db = $database;
	}

}
