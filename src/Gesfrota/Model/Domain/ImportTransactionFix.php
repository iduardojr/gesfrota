<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Util\Format;

/**
 * Transação de Manutenção
 * 
 * @Entity
 */
class ImportTransactionFix extends ImportTransactionItem {
    
    /**
     * Produto
     * @var string
     */
    const TYPE_PRODUCT = 'P';
    
    /**
     * Serviço
     * @var string
     */
    const TYPE_SERVICE = 'S';
    
    /**
     * @ManyToOne(targetEntity="ImportTransactionFix", cascade={"persist", "detach"})
     * @JoinColumn(name="transaction_parent_id", referencedColumnName="transaction_id")
     * @var ImportTransactionFix
     */
    protected $transactionParent;
    
    /**
     * @Column(name="transaction_fixtype")
     * @var string
     */
    protected $transactionFixtype; 
    
    /**
     * @Column(name="supplier_type")
     * @var string
     */
    protected $supplierType;
    
    /**
     * @Column(name="item_type")
     * @var string
     */
    protected $itemType;
    
    /**
     * @return ImportTransactionFix
     */
    public function getTransactionParent()
    {
        return $this->transactionParent;
    }
    
    /**
     * @return string
     */
    public function getTransactionFixtype()
    {
        return $this->transactionFixtype;
    }
    
    /**
     * @return string
     */
    public function getSupplierType()
    {
        return $this->supplierType;
    }

    /**
     * @return string
     */
    public function getItemType()
    {
        return $this->itemType;
    }
    
    /**
     * @see ImportTransactionItem::setTransactionVehicle()
     */
    public function setTransactionVehicle(Vehicle $vehicle)
    {
        parent::setTransactionVehicle($vehicle);
        if ($this->transactionParent) {
            $this->transactionParent->setTransactionVehicle($vehicle);
        }
    }

    /**
     * @param ImportTransactionFix $parent
     */
    public function setTransactionParent(ImportTransactionFix $parent)
    {
        $this->transactionParent = $parent;
    }

    /**
     * @param string $type
     */
    public function setTransactionFixtype($type)
    {
        $this->transactionFixtype = $type;
    }
    
    /**
     * @param string $type
     */
    public function setSupplierType($type)
    {
        $this->supplierType = $type;
    }
    
    /**
     * @param string $type
     * @throws \InvalidArgumentException
     */
    public function setItemType($type)
    {
        if ($type !== null  && ! in_array($type, [self::TYPE_PRODUCT, self::TYPE_SERVICE]) ) {
            throw new \InvalidArgumentException('Item type [' . $type . '] not is allowed');
        }
        $this->itemType = $type;
    }
    
    
    /**
     * @return float
     */
    public function getItemPriceLabor() 
    {
        return $this->transactionParent ? $this->transactionParent->getItemTotal() : 0;
    }
    
    /**
     * @return float
     */
    public function getItemPriceParts() 
    {
        return $this->itemTotal;
    }
    
    /**
     * @see ImportTransactionItem::getItemTotal()
     */
    public function getItemTotal() 
    {
        return $this->itemTotal + $this->getItemPriceLabor();    
    }

    /**
     * @see ImportTransactionItem::toTransform()
     */
    public function toTransform(array $data)
    {
        $this->setTransactionCostCenter($data[0]);
        $this->setTransactionDate(new \DateTime($data[1]));
        $this->setTransactionFixtype(ucwords(strtolower($data[2])));
        $this->setVehiclePlate(str_replace('-', '', $data[3]));
        $this->setVehicleDescription($data[4]);
        $this->setSupplierNif(Format::CNPJ($data[5]));
        $this->setSupplierName($data[6]);
        $this->setSupplierType($data[7]);
        $this->setSupplierPlace($data[8], $data[9]);
        if ($this->isModelDetailed()) {
            $this->setItemType(strtoupper(substr($data[10], 0, 1)));
            $this->setItemDescription($data[11]);
            $this->setItemQuantity((float) str_replace(',', '.', $data[12]));
            $this->setItemPrice((float) str_replace(',', '.', $data[13]));
            $this->setItemTotal((float) str_replace(',', '.', $data[14]));
        } elseif($this->isModelDiscriminated()) {
            $parent = clone $this;
            
            $this->setItemType(ImportTransactionFix::TYPE_PRODUCT);
            $this->setItemTotal((float) str_replace(',', '.', $data[10]));
            
            $parent->setItemType(ImportTransactionFix::TYPE_SERVICE);
            $parent->setItemTotal((float) str_replace(',', '.', $data[11]));
            if ( $parent->getItemTotal() > 0 ) {
                $this->setTransactionParent($parent);
            }
        } else {
            $this->setItemTotal((float) str_replace(',', '.', $data[10]));
        }
    }
    
    /**
     * @see ImportTransactionItem::getData()
     */
    public function getData() 
    {
        $data = [];
        $data[] = $this->getTransactionAgency() . '<' . $this->getTransactionCostCenter() . '>';
        $data[] = $this->getTransactionDate()->format('d/m/Y H:i:s');
        $data[]= $this->getTransactionFixtype();
        $data[] = $this->getVehiclePlate();
        $data[] = $this->getTransactionVehicle() ? $this->getTransactionVehicle()->getDescription() : $this->getVehicleDescription();
        $data[] = $this->getSupplierNif();
        $data[] = $this->getSupplierName();
        $data[] = $this->getSupplierType();
        $data[] = $this->supplierCity;
        $data[]= $this->supplierUF;
        
        if ($this->isModelDetailed()) {
            $data[] = $this->getItemType();
            $data[] = $this->getItemDescription();
            $data[]= number_format($this->getItemQuantity(), 2, ',', '.');
            $data[]= 'R$ ' . number_format($this->getItemPrice(), 2, ',', '.');
        } elseif($this->isModelDiscriminated()) {
            $data[] = 'R$ ' . number_format($this->getItemPriceParts(), 2, ',', '.');
            $data[] = 'R$ ' . number_format($this->getItemPriceLabor(), 2, ',', '.');
        } 
        $data[] = 'R$ ' . number_format($this->getItemTotal(), 2, ',', '.');
        
        return $data;
    }
    
    /**
     * @return boolean
     */
    public function isModelSummarized() 
    {
        return count($this->transactionImport->getHeader()) == 11;
    }
    
    /**
     * @return boolean
     */
    public function isModelDiscriminated() 
    {
        return count($this->transactionImport->getHeader()) == 13;
    }
    
    /**
     * @return boolean
     */
    public function isModelDetailed() 
    {
        return count($this->transactionImport->getHeader()) == 15;
    }
    
}
?>