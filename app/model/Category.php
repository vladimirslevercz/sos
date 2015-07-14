<?php

namespace App\Model;

/**
 * Persistent object Category.
 */
class Category extends \Nette\Database\Table\Selection {
    private $table = "category";
    private $db;
    
    public function __construct(\Nette\Database\Context $database) {
        parent::__construct($database, $database->getConventions(), $this->table);
        $this->db = $database;
    }
}
