<?php
namespace Gesfrota\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Importação de Transações de Abastecimento
 * 
 * @Entity
 */
class ImportSupply extends ImportTransaction {

    /**
     * @OneToMany(targetEntity="ImportTransactionFuel", mappedBy="transactionImport", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var ArrayCollection
     */
    protected $items;
    
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