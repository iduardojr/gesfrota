<?php
namespace Gesfrota\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Importação de frota
 * 
 * @Entity
 */
class ImportFleet extends Import {

    /**
     * @OneToMany(targetEntity="ImportFleetItem", mappedBy="import", fetch="EXTRA_LAZY", cascade={"persist"})
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
     * @param Agency $agency
     */
    public function __construct(Agency $agency = null) {
        parent::__construct();
        $this->agency = $agency;
    }
    
    /**
     * @return Agency
     */
    public function getAgency() {
        return $this->agency;
    }

    /**
     * @param Agency $agency
     */
    public function setAgency(Agency $agency) {
        $this->agency = $agency;
    }
    

    /**
     * @see Import::getAmountImported()
     */
    public function getAmountImported() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq('reference', null));
        return $this->items->matching($criteria)->count();
    }
    
    /**
     * 
     * @see Import::getAmountAppraised()
     */
    public function getAmountAppraised() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->neq('status', null));
        return $this->items->matching($criteria)->count();
    }
    
}
?>