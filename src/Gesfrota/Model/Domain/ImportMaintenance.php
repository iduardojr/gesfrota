<?php
namespace Gesfrota\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Importação de Transações de Manutenção
 * 
 * @Entity
 */
class ImportMaintenance extends ImportTransaction {

    /**
     * @var string
     */
    const SERVICE_TYPE = 'Manutenção';
    
    /**
     * @OneToMany(targetEntity="ImportTransactionFix", mappedBy="transactionImport", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var ArrayCollection
     */
    protected $items;
    
    /**
     * @see Import::getAmountItems()
     */
    public function getAmountItems()
    {   
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('transactionParent'));
        return $this->items->matching($criteria)->count();
    }
    
    /**
     * @return integer
     */
    public function getAmountImported() {
        $criteria = Criteria::create();
        $criteria->where(Criteria::expr()->isNull('transactionParent'));
        $criteria->andWhere(Criteria::expr()->neq('transactionAgency', null));
        return $this->items->matching($criteria)->count();
    }
    
    /**
     * @see ImportTransaction::create()
     */
    public function create(array $data = null)
    {
        return new ImportTransactionFix($this, $data);
    }

    
}
?>