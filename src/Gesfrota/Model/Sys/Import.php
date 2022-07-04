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
 * @EntityListeners({"Gesfrota\Model\Listener\ImportListener"})
 */
class Import extends Entity {

    /**
     * Carregado
     * @var integer
     */
    const UPLOADED = 1;
    
    /**
     * Pré-processado
     * @var integer
     */
    const PREPROCESSED = 2;
    
    /**
     * Finalizado
     * 
     * @var integer
     */
    const FINISHED = 4;
    
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
     * @Column(type="integer")
     * @var integer
     */
    protected $status;
    
    /**
     * @OneToMany(targetEntity="ImportItem", mappedBy="import", cascade={"all"})
     * @var ArrayCollection
     */
    protected $items;
    
    /**
     * @Column(name="created_at", type="datetime")
     * @var \DateTime
     */
    protected $createdAt;
    
    
    public function __construct() {
        parent::__construct();
        $this->createdAt = new \DateTime();
        $this->items = new ArrayCollection();
        $this->status = self::UPLOADED;
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
     * @return ArrayCollection
     */
    public function getItems() {
        return $this->items;
    }
    
    /**
     * @return integer
     */
    public function getStatus() {
        return $this->status;
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
     * @param integer $status
     * @throws \DomainException
     */
    public function setStatus($status) {
        if (!self::isStatusAllowed($status)) {
            throw new \DomainException('The ' . $status . ' is not status allowed.');
        }
        $this->status = $status;
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
    
    /**
     * Verifica se o status é permitido
     * @param integer $status
     * @return bool
     */
    public static function isStatusAllowed( int $status ) {
        return array_key_exists($status, self::getStatusAllowed());
    }
    
    /**
     * Obtem a lista de frotas permitidas
     *
     * @return string[]
     */
    public static function getStatusAllowed() {
        return [self::UPLOADED => 'Carregado',
                self::PREPROCESSED => 'Pré-processado',
                self::FINISHED => 'Finalizado'
        ];
    }
    


}
?>