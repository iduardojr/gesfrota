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
     * @var string
     */
    const SERVICE_TYPE = 'Abastecimento';

    /**
     * @OneToMany(targetEntity="ImportTransactionFuel", mappedBy="transactionImport", fetch="EXTRA_LAZY", cascade={"persist"})
     * @var ArrayCollection
     */
    protected $items;
    
    /**
     * @see ImportTransaction::create()
     */
    public function create(array $data = null)
    {
        return new ImportTransactionFuel($this, $data);
    }
    
}
?>