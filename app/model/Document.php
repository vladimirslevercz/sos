<?php

namespace App\Model;
use Nette\Database\Table\ActiveRow;
use Nette\Http\FileUpload;
use Nette\Security\User;
use Nette\Utils\Random;

/**
 * Persistent object File.
 */
class Document extends \Nette\Database\Table\Selection
{


    private $db, $user;
    private $table = "document";

    const SAVE_DIR = WWW_DIR . "/content/document/";
    const ENABLED_EXTENSION = ['doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'zip', 'ppt', 'pptx', 'pdf', 'txt'];

    public function __construct(\Nette\Database\Context $database, User $user)
    {
        parent::__construct($database, $database->getConventions(), $this->table);
        $this->db = $database;
        $this->user = $user;
    }

    public function deleteFile(ActiveRow $document)
    {
        $filePath = self::SAVE_DIR . $document->path;
        if (file_exists($filePath)) {
            return unlink($filePath);
        } else {
            return false;
        }
    }

    public function saveFile(FileUpload $fileUpload, $niceName = null, $private=false)
    {
        $fileInfo = new \SplFileInfo($fileUpload->getName());

        if (!$fileUpload->isOk()) {
            throw new \Exception('Soubor se nepodařilo nahrát.');
        }

        if (! in_array($fileInfo->getExtension(), self::ENABLED_EXTENSION)) {
            throw new \InvalidArgumentException('Sobory typu '.$fileInfo->getExtension().' nejsou povolené.');
        }

        unset($fileInfo);

        $filePath = $this->user->getId(). DIRECTORY_SEPARATOR .Random::generate(4).'_'.$fileUpload->getSanitizedName();
        $niceName = $niceName ?: $fileUpload->getName();

        $document = $this->insert( [
            'path' => $filePath,
            'user_id' => $this->user->getId(),
            'nice_name' => $niceName
        ]);

        $fileUpload->move(self::SAVE_DIR . $filePath);
        return $document;
    }

}
