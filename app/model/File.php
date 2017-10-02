<?php

namespace App\Model;

/**
 * Persistent object File.
 */
class File extends \Nette\Database\Table\Selection {
    private $db;
    private $table = "file";

    const SAVE_DIR = "../content/attachements/";
    const ENABLED_EXTENSION = "doc docx xls xlsx jpg jpeg png zip ppt pptx pdf txt";

	public function __construct(\Nette\Database\Context $database) {
		parent::__construct($database, $database->getConventions(), $this->table);
		$this->db = $database;
	}

}
