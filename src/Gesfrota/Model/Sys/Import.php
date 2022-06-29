<?php
namespace Gesfrota\Model\Sys;

use Gesfrota\Model\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Importação
 * 
 * @Entity
 * @Table(name="imports")
 */
class Import extends Entity {
    
    /**
     * Diretório relativo ao link
     * 
     * @var string
     */
    private static $DIR_BASE;
    
    /**
     * Diretório raiz dos arquivos de importação
     * 
     * @var string
     */
    private static $DIR_ROOT;
    
    /**
     * @Column(type="string")
     * @var string
     */
    protected $description;
    
    /**
     * @Column(name="created_at", type="datetime")
     * @var \DateTime
     */
    protected $createdAt;
    
    /**
     * @Column(name="filename", type="string")
     * @var string
     */
    protected $fileName;
    
    /**
     * @Column(name="filesize", type="integer")
     * @var integer
     */
    protected $fileSize;
    
    /**
     * @Column(type="array")
     * @var array
     */
    protected $header;
    
    /**
     * @OneToMany(targetEntity="ImportItem", mappedBy="import", cascade={"all"})
     * @var ArrayCollection
     */
    protected $items;
    
    
    public function __construct() {
        parent::__construct();
        $this->createdAt = new \DateTime();
        $this->items = new ArrayCollection();
    }
    
    /**
     * @return string
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getFileName() {
        return $this->fileName;
    }

    /**
     * @return integer
     */
    public function getFileSize() {
        return $this->fileSize;
    }
    
    /**
     * @return array
     */
    public function getHeader() {
        return $this->header;
    }

    /**
     * @return array
     */
    public function getItems() {
        return $this->items->toArray();
    }

    /**
     * @param string $text
     */
    public function setDescription($text) {
        $this->description = $text;
    }
    
    /**
     * @param string $file
     */
    public function setFileName($fileName) {
        $this->fileName = $fileName;
        $this->fileSize = filesize(self::$DIR_ROOT . $fileName);
        
        $file = fopen(self::$DIR_ROOT . $fileName, 'r', true);
        
        $line = fgetcsv($file, 0, ";");
        if ( $line ) {
            $this->header = $line;
        }
        while ($data = fgetcsv($file, 0, ";")) {
            foreach($data as $i => $val) {
                $data[$i] = utf8_encode($val);
            }
            $this->items->add(new ImportItem($this, $data));
        }
    }
    
    /**
     * @return integer
     */
    public function getAmountItems() {
        return $this->items->count();
    }
    
    /**
     * @return integer
     */
    public function getAmountImported() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq('reference', null));
        return $this->items->matching($criteria)->count();
    }

    /**
     * @param string $dir
     */
    public static function setDir($root, $base = '/imports') {
        $base = rtrim($base, '/') . '/';
        self::$DIR_ROOT = rtrim($root, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $base);
        self::$DIR_BASE = $base;
    }
    
    /**
     * @return string
     */
    public static function getDirRoot() {
        return self::$DIR_ROOT;
    }
    
    /**
     * @return string
     */
    public static function getDirBase() {
        return self::$DIR_BASE;
    }
}
?>