<?php
namespace Gesfrota\Model\Domain;

/**
 * Transação de Abastecimento
 * 
 * @Entity
 * @Table(name="import_transactions_fuel")
 * @EntityListeners({"Gesfrota\Model\Listener\ImportItemListener"})
 */
class ImportTransactionFuel extends ImportTransactionItem {
    
    /**
     * @Column(name="vehicle_odometer", type="integer")
     * @var integer
     */
    protected $vehicleOdometer;
    
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
     * @Column(name="supplier_name")
     * @var string
     */
    protected $supplierName;
    
    /**
     * @Column(name="supplier_nif")
     * @var string
     */
    protected $supplierNif;
    
    /**
     * @Column(name="supplier_city")
     * @var string
     */
    protected $supplierCity;
    
    /**
     * @Column(name="supplier_uf")
     * @var string
     */
    protected $supplierUF;
    
    /**
     * @Column(name="item_description")
     * @var string
     */
    protected $itemDescription;
    
    /**
     * @Column(name="item_quantity", type="float")
     * @var float
     */
    protected $itemQuantity;
    
    /**
     * @Column(name="item_price", type="float")
     * @var float
     */
    protected $itemPrice;
    
    /**
     * @Column(name="item_total", type="float")
     * @var float
     */
    protected $itemTotal;
    

    /**
     * @return number
     */
    public function getVehicleOdometer()
    {
        return $this->vehicleOdometer;
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
     * @return string
     */
    public function getSupplierName()
    {
        return $this->supplierName;
    }

    /**
     * @return string
     */
    public function getSupplierNif()
    {
        return $this->supplierNif;
    }

    /**
     * @return string
     */
    public function getSupplierPlace()
    {
        return $this->supplierCity . '/' . $this->supplierUF;
    }

    /**
     * @return string
     */
    public function getItemDescription()
    {
        return $this->itemDescription;
    }

    /**
     * @return number
     */
    public function getItemQuantity()
    {
        return $this->itemQuantity;
    }

    /**
     * @return number
     */
    public function getItemPrice()
    {
        return $this->itemPrice;
    }

    /**
     * @return number
     */
    public function getItemTotal()
    {
        return $this->itemTotal;
    }

    /**
     * @param number $odometer
     */
    public function setVehicleOdometer($odometer)
    {
        $this->vehicleOdometer = $odometer;
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
     * @param string $name
     */
    public function setSupplierName($name)
    {
        $this->supplierName = $name;
    }

    /**
     * @param string $nif
     */
    public function setSupplierNif($nif)
    {
        $this->supplierNif = $nif;
    }

    /**
     * @param string $city
     * @param string $uf
     */
    public function setSupplierPlace($city, $uf)
    {
        $this->supplierCity = $city;
        $this->supplierUF = $uf;
    }

    /**
     * @param string $description
     */
    public function setItemDescription($description)
    {
        $this->itemDescription = $description;
    }

    /**
     * @param number $quantity
     */
    public function setItemQuantity($quantity)
    {
        $this->itemQuantity = $quantity;
    }

    /**
     * @param number $price
     */
    public function setItemPrice($price)
    {
        $this->itemPrice = $price;
    }

    /**
     * @param number $total
     */
    public function setItemTotal($total)
    {
        $this->itemTotal = $total;
    }
    
    /**
     * @see ImportTransactionItem::toTransform()
     */
    public function toTransform(array $data)
    {
        parent::toTransform($data);
        $short = 18-count($this->transactionImport->getHeader());
        $expanded = ! $short > 0; 
        $this->setDriverNif($expanded ? $data[5] : '');
        $this->setDriverName( $data[6-($expanded ? 0 : 1)] );
        $this->setSupplierNif($expanded ? $data[7-($expanded ? 0 : 1)] : '');
        $this->setSupplierName($data[8-$short]);
        $this->setSupplierPlace($data[9-$short], $data[10-$short]);
        $this->setItemDescription(ucwords(strtolower(str_ireplace(' comum', '', $data[11-$short]))));
        $this->setItemQuantity((float) str_replace(',', '.', $data[12-$short]));
        $this->setItemPrice((float) str_replace(',', '.', $data[13-$short]));
        $this->setItemTotal((float) str_replace(',', '.', $data[14-$short]));
        $this->setVehicleOdometer((int) $data[15-$short]);
        $this->setVehicleDistance((int) $data[16-$short]);
        $this->setVehicleEfficiency((float) str_replace(',', '.', $data[17-$short]));
        
    }
    
    /**
     * @see ImportTransactionItem::getData()
     */
    public function getData() 
    {
        $data = parent::getData();
        $short = 18-count($this->transactionImport->getHeader());
        $expanded = ! $short > 0; 
        if ($expanded) {
            $data[5] = $this->getDriverNif();
        }
        $data[6-($expanded ? 0 : 1)] = $this->getDriverName();
        $data[7-($expanded ? 0 : 1)] = $this->getSupplierNif();
        $data[8-$short] = $this->getSupplierName();
        $data[9-$short] = $this->supplierCity;
        $data[10-$short]= $this->supplierUF;
        $data[11-$short]= $this->getItemDescription();
        $data[12-$short]= number_format($this->getItemQuantity(), 2, ',', '.');
        $data[13-$short]= 'R$ ' . number_format($this->getItemPrice(), 3, ',', '.');
        $data[14-$short]= 'R$ ' . number_format($this->getItemTotal(), 2, ',', '.');
        $data[15-$short]= $this->getVehicleOdometer();
        $data[16-$short]= $this->getVehicleDistance();
        $data[17-$short]= number_format($this->getVehicleEfficiency(), 2, ',', '.');
        return $data;
    }

    
}
?>