<?php
namespace Gesfrota\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Gesfrota\Model\Entity;

/**
 * Importação
 * 
 * @Entity
 * @Table(name="imports")
 * @EntityListeners({"Gesfrota\Model\Listener\ImportListener"})
 */
class Import extends Entity {

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
     * @OneToMany(targetEntity="ImportItem", mappedBy="import", cascade={"persist"})
     * @var ArrayCollection
     */
    protected $items;
    
    /**
     * @ManyToOne(targetEntity="Agency")
     * @JoinColumn(name="agency_id", referencedColumnName="id")
     * @var Agency
     */
    protected $agency;
    
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
    
    /**
     * @param Agency $agency
     */
    public function __construct(Agency $agency = null) {
        parent::__construct();
        $this->agency = $agency;
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
     * @return Agency
     */
    public function getAgency() {
        return $this->agency;
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
     * @param Agency $agency
     */
    public function setAgency(Agency $agency) {
        $this->agency = $agency;
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
     * @return integer
     */
    public function getAmountAppraised() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq('status', null));
        return $this->items->matching($criteria)->count();
    }
    

}
?>