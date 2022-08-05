<?php
namespace Gesfrota\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Gesfrota\Model\Entity;

/**
 * Importação
 * 
 * @Entity
 * @Table(name="imports")
 * @EntityListeners({"Gesfrota\Model\Listener\ImportListener"})
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"F" = "ImportFleet"})
 */
abstract class Import extends Entity {

    /**
     * Diretório relativo ao link
     * @var string
     */
    const DIR = '/imports/';
    
    /**
     * @Column(type="string")
     * @var string
     */
    protected $description;
    
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
     * @Column(type="json_array")
     * @var array
     */
    protected $header;
    
    /**
     * @Column(type="boolean")
     * @var boolean
     */
    protected $finished;
    
    /**
     * @var ArrayCollection
     */
    protected $items;
    
    /**
     * @Column(name="created_at", type="datetime")
     * @var \DateTime
     */
    protected $createdAt;
    
    /**
     * @Column(name="finished_at", type="datetime")
     * @var \DateTime
     */
    protected $finishedAt;
    
    public function __construct() {
        parent::__construct();
        $this->items = new ArrayCollection();
        $this->finished = false;
        $this->createdAt = new \DateTime();
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
     * @return \DateTime
     */
    public function getFinishedAt() {
        return $this->finishedAt;
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
     * @return ArrayCollection
     */
    public function getItems() {
        return $this->items;
    }
    
    /**
     * @return boolean
     */
    public function getFinished() {
        return $this->finished;
    }

    /**
     * @param string $text
     */
    public function setDescription($text) {
        $this->description = $text;
    }
    
    /**
     * @param array $data
     */
    public function setHeader(array $data) {
        $this->header = $data;
    }
    
    /**
     * @param string $file
     */
    public function setFileName($fileName) {
        $this->fileName = $fileName;
    }
    
    /**
     * @param integer $fileSize
     */
    public function setFileSize($fileSize) {
        $this->fileSize = $fileSize;
    }
    
    /**
     * @throws \DomainException
     */
    public function toFinish() {
        if ($this->finished === true ) {
            throw new \DomainException('The import cannot be finished.');
        }
        $this->finished = true;
        $this->finishedAt = new \DateTime();
    }
    
    /**
     * @return integer
     */
    public function getAmountItems() {
        return $this->items->count();
    }
    

}
?>