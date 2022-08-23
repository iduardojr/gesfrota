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
     * @Column(name="transaction_fixtype")
     * @var string
     */
    protected $transactionFixtype; 
    
    /**
     * @ManyToOne(targetEntity="ImportTransactionFix", cascade={"persist", "detach"})
     * @JoinColumn(name="transaction_parent_id", referencedColumnName="transaction_id")
     * @var ImportTransactionFix
     */
    protected $transactionParent;
    
    /**
     * @return string
     */
    public function getTransactionFixtype()
    {
        return $this->transactionFixtype;
    }

    /**
     * @return ImportTransactionFix
     */
    public function getTransactionParent()
    {
        return $this->transactionParent;
    }

    /**
     * @param ImportTransactionFix $parent
     */
    public function setTransactionParent(ImportTransactionFix $parent)
    {
        $this->transactionParent = $parent;
        if ($this->itemTotal > ($this->getItemQuantity() * $this->getItemPrice()) ) {
            $this->itemTotal = $this->itemTotal - $this->getItemPriceLabor();
        }
    }

    /**
     * @param string $transactionFixtype
     */
    public function setTransactionFixtype($transactionFixtype)
    {
        $this->transactionFixtype = $transactionFixtype;
    }
    
    public function setTransactionVehicle(Vehicle $vehicle) 
    {
        parent::setTransactionVehicle($vehicle);
        if ($this->transactionParent) {
            $this->transactionParent->setTransactionVehicle($vehicle);
        }
    }
    
    /**
     * @see ImportTransactionItem::setItemTotal()
     */
    public function setItemTotal($total) 
    {
        $this->itemTotal = $total == ($this->getItemQuantity() * $this->getItemPrice()) ? $total : $total - $this->getItemPriceLabor();
    }
    
    /**
     * @return float
     */
    public function getItemPriceLabor() 
    {
        if ($this->itemType == self::TYPE_SERVICE && $this->transactionParent == null) {
            return $this->getItemPrice();
        }
        return $this->transactionParent ? $this->transactionParent->getItemPrice() : 0;
    }
    
    /**
     * @see ImportTransactionItem::getItemTotal()
     */
    public function getItemTotal() 
    {
        if ($this->itemType == self::TYPE_SERVICE && $this->transactionParent == null) {
            return $this->itemTotal;
        }
        return $this->itemTotal + $this->getItemPriceLabor();    
    }

    /**
     * @see ImportTransactionItem::toTransform()
     */
    public function toTransform(array $data)
    {
        $short = 15-count($this->transactionImport->getHeader());
        $this->setTransactionCostCenter($data[0]);
        $this->setTransactionDate(new \DateTime($data[1]));
        $this->setVehiclePlate(str_replace('-', '', $data[2]));
        $this->setVehicleDescription($data[3]);
        $this->setSupplierNif(Format::CNPJ($data[4]));
        $this->setSupplierName($data[5]);
        $this->setSupplierPlace($data[6], $data[7]);
        $this->setTransactionFixtype(ucwords(strtolower($data[8])));
        $this->setItemType($short ? ImportTransactionFix::TYPE_PRODUCT : strtoupper(substr($data[9], 0, 1)));
        $this->setItemDescription($data[10-$short]);
        $this->setItemQuantity((float) str_replace(',', '.', $data[11-$short]));
        $this->setItemPrice((float) str_replace(',', '.', $data[12-$short]));
        if ( $short ) {
            $parent = clone $this;
            $parent->setItemType(ImportTransactionFix::TYPE_SERVICE);
            $parent->setItemPrice((float) str_replace(',', '.', $data[13-$short]));
            $parent->setItemTotal($parent->getItemQuantity()*$parent->getItemPrice());
            if ( $parent->getItemPrice() > 0 ) {
                $this->setTransactionParent($parent);
            }
        }
        $this->setItemTotal(((float) str_replace(',', '.', $data[14-$short])));
    }
    
    /**
     * @see ImportTransactionItem::getData()
     */
    public function getData() 
    {
        $data = [];
        $short = 15-count($this->transactionImport->getHeader());
        
        $data[0] = $this->getTransactionAgency() . '<' . $this->getTransactionCostCenter() . '>';
        $data[1] = $this->getTransactionDate()->format('d/m/Y H:i:s');
        $data[2] = $this->getVehiclePlate();
        $data[3] = $this->getTransactionVehicle() ? $this->getTransactionVehicle()->getDescription() : $this->getVehicleDescription();
        $data[4] = $this->getSupplierNif();
        $data[5] = $this->getSupplierName();
        $data[6] = $this->supplierCity;
        $data[7]= $this->supplierUF;
        $data[8]= $this->getTransactionFixtype();
        if (! $short) {
            $data[9] = $this->getItemType() == ImportTransactionFix::TYPE_PRODUCT ? 'Produto' : 'Serviço';
        }
        $data[10-$short]= $this->getItemDescription();
        $data[11-$short]= number_format($this->getItemQuantity(), 2, ',', '.');
        $data[12-$short]= 'R$ ' . number_format($this->getItemPrice(), 2, ',', '.');
        $data[13-$short]= 'R$ ' . number_format($this->getItemPriceLabor(), 2, ',', '.');
        $data[14-$short]= 'R$ ' . number_format($this->getItemTotal(), 2, ',', '.');
        return $data;
    }

    
}
?>