<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Util\Format;

/**
 * Transação de Abastecimento
 * 
 * @Entity
 */
class ImportTransactionFuel extends ImportTransactionItem {
    
    /**
     * @Column(name="vehicle_distance", type="integer")
     * @var integer
     */
    protected $vehicleDistance;
    
    /**
     * @Column(name="vehicle_efficiency", type="float")
     * @var float
     */
    protected $vehicleEfficiency;
    
    /**
     * @Column(name="driver_name")
     * @var string
     */
    protected $driverName;
    
    /**
     * @Column(name="driver_nif")
     * @var string
     */
    protected $driverNif;
    
    /**
     * @see ImportTransactionItem::setItemType()
     */
    public function setItemType($itemType) 
    {
        throw new \BadMethodCallException('method ' . __METHOD__ . ' not is callback');
    }
    
    /**
     * @return number
     */
    public function getVehicleDistance()
    {
        return $this->vehicleDistance;
    }

    /**
     * @return number
     */
    public function getVehicleEfficiency()
    {
        return $this->vehicleEfficiency;
    }

    /**
     * @return string
     */
    public function getDriverName()
    {
        return $this->driverName;
    }

    /**
     * @return string
     */
    public function getDriverNif()
    {
        return $this->driverNif;
    }


    /**
     * @param number $distance
     */
    public function setVehicleDistance($distance)
    {
        $this->vehicleDistance = $distance;
    }

    /**
     * @param number $efficiency
     */
    public function setVehicleEfficiency($efficiency)
    {
        $this->vehicleEfficiency = $efficiency;
    }

    /**
     * @param string $name
     */
    public function setDriverName($name)
    {
        $this->driverName = $name;
    }

    /**
     * @param string $nif
     */
    public function setDriverNif($nif)
    {
        $this->driverNif = $nif;
    }

    /**
     * @see ImportTransactionItem::toTransform()
     */
    public function toTransform(array $data)
    {
        $short = 16-count($this->transactionImport->getHeader());
        $expanded = ! $short > 0; 
        $this->setTransactionCostCenter($data[0]);
        $this->setTransactionDate(new \DateTime($data[1]));
        $this->setVehiclePlate(str_replace('-', '', $data[2]));
        $this->setVehicleDescription($data[3]);
        $this->setDriverNif($expanded ? Format::CPF($data[4]) : '');
        $this->setDriverName( $data[5-($expanded ? 0 : 1)] );
        $this->setSupplierNif($expanded ? Format::CNPJ($data[6-($expanded ? 0 : 1)]) : '');
        $this->setSupplierName($data[7-$short]);
        $this->setSupplierPlace($data[8-$short], $data[9-$short]);
        $this->setItemDescription(ucwords(strtolower(str_ireplace(' comum', '', $data[10-$short]))));
        $this->setItemQuantity((float) str_replace(',', '.', $data[11-$short]));
        $this->setItemPrice((float) str_replace(',', '.', $data[12-$short]));
        $this->setItemTotal((float) str_replace(',', '.', $data[13-$short]));
        $this->setVehicleDistance((int) $data[14-$short]);
        $this->setVehicleEfficiency((float) str_replace(',', '.', $data[15-$short]));
        
    }
    
    /**
     * @see ImportTransactionItem::getData()
     */
    public function getData() 
    {
        $data = [];
        $short = 16-count($this->transactionImport->getHeader());
        $expanded = ! $short > 0; 
        
        $data[0] = $this->getTransactionAgency() . '<' . $this->getTransactionCostCenter() . '>';
        $data[1] = $this->getTransactionDate()->format('d/m/Y H:i:s');
        $data[2] = $this->getVehiclePlate();
        $data[3] = $this->getTransactionVehicle() ? $this->getTransactionVehicle()->getDescription() : $this->getVehicleDescription();
        if ($expanded) {
            $data[4] = $this->getDriverNif();
        }
        $data[5-($expanded ? 0 : 1)] = $this->getDriverName();
        $data[6-($expanded ? 0 : 1)] = $this->getSupplierNif();
        $data[7-$short] = $this->getSupplierName();
        $data[8-$short] = $this->supplierCity;
        $data[9-$short]= $this->supplierUF;
        $data[10-$short]= $this->getItemDescription();
        $data[11-$short]= number_format($this->getItemQuantity(), 2, ',', '.');
        $data[12-$short]= 'R$ ' . number_format($this->getItemPrice(), 3, ',', '.');
        $data[13-$short]= 'R$ ' . number_format($this->getItemTotal(), 2, ',', '.');
        $data[15-$short]= number_format($this->getVehicleDistance(), 0, '', '.');
        $data[16-$short]= number_format($this->getVehicleEfficiency(), 2, ',', '.');
        var_dump($data);
        exit;
        return $data;
    }

    
}
?>