<?php
namespace Gesfrota\Model\Domain;

/**
 * Item de uma transação
 * 
 * @Entity
 * @Table(name="import_transaction_items")
 * @EntityListeners({"Gesfrota\Model\Listener\ImportItemListener"})
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="transaction_service", type="string")
 * @DiscriminatorMap({"S" = "ImportTransactionFuel", "M" = "ImportTransactionFix"})
 */
abstract class ImportTransactionItem {
    
    /**
     * Produto
     */
    const TYPE_PRODUCT = 'P';
    
    /**
     * Serviço
     */
    const TYPE_SERVICE = 'S';
    
    /**
     * @Id
     * @GeneratedValue
     * @Column(name="transaction_id", type="integer")
     * @var integer
     */
    protected $transactionId;
    
    /**
     * @Column(name="transaction_date", type="datetime")
     * @var \DateTime
     */
    protected $transactionDate;
    
    /**
     * @ManyToOne(targetEntity="Agency")
     * @JoinColumn(name="transaction_agency_id", referencedColumnName="id")
     * @var Agency
     */
    protected $transactionAgency;
    
    /**
     * @Column(name="transaction_cost_center")
     * @var string
     */
    protected $transactionCostCenter;
    
    /**
     * @ManyToOne(targetEntity="Vehicle")
     * @JoinColumn(name="transaction_vehicle_id", referencedColumnName="id")
     * @var Vehicle
     */
    protected $transactionVehicle;
    
    /**
     * @ManyToOne(targetEntity="ImportTransaction", inversedBy="items")
     * @JoinColumn(name="transaction_import_id", referencedColumnName="id")
     * @var ImportSupply
     */
    protected $transactionImport;
    
    /**
     * @Column(name="vehicle_plate")
     * @var string
     */
    protected $vehiclePlate;
    
    /**
     * @Column(name="vehicle_description")
     * @var string
     */
    protected $vehicleDescription;
    
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
     * @Column(name="item_type")
     * @var string
     */
    protected $itemType;
    
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
     * @param ImportTransaction $import
     * @param array $data
     */
    public function __construct(ImportTransaction $import, array $data = null) {
        $this->setTransactionImport($import);
        $this->setItemType(self::TYPE_PRODUCT);
        if ($data !== null ) {
            $this->toTransform($data);
        }
    }

    /**
     * @return number
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return \DateTime
     */
    public function getTransactionDate()
    {
        return $this->transactionDate;
    }

    /**
     * @return Agency
     */
    public function getTransactionAgency()
    {
        return $this->transactionAgency;
    }

    /**
     * @return string
     */
    public function getTransactionCostCenter()
    {
        return $this->transactionCostCenter;
    }
    
    /**
     * @return Vehicle
     */
    public function getTransactionVehicle()
    {
        return $this->transactionVehicle;
    }
    
    /**
     * @return ImportTransaction
     */
    public function getTransactionImport()
    {
        return $this->transactionImport;
    }
    
    /**
     * @return string
     */
    public function getVehiclePlate()
    {
        return $this->vehiclePlate;
    }
    
    /**
     * @return string
     */
    public function getVehicleDescription()
    {
        return $this->vehicleDescription;
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
    public function getItemType()
    {
        return $this->itemType;
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
     * @param number $id
     */
    public function setTransactionId($id)
    {
        $this->transactionId = $id;
    }

    /**
     * @param \DateTime $date
     */
    public function setTransactionDate(\DateTime $date)
    {
        $this->transactionDate = $date;
    }

    /**
     * @param Agency $agency
     */
    public function setTransactionAgency(Agency $agency)
    {
        $this->transactionAgency = $agency;
    }

    /**
     * @param string $costCenter
     */
    public function setTransactionCostCenter($costCenter)
    {
        $this->transactionCostCenter = $costCenter;
    }

    
    /**
     * @param Vehicle $vehicle
     */
    public function setTransactionVehicle(Vehicle $vehicle)
    {
        $this->transactionVehicle = $vehicle;
    }
    
    /**
     * @param ImportTransaction $import
     */
    public function setTransactionImport(ImportTransaction $import)
    {
        $this->transactionImport = $import;
    }
    
    /**
     * @param string $plate
     */
    public function setVehiclePlate($plate)
    {
        $this->vehiclePlate = $plate;
    }
    
    /**
     * @param string $description
     */
    public function setVehicleDescription($description)
    {
        $this->vehicleDescription = $description;
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
     * @param string $itemType
     * @throws \InvalidArgumentException
     */
    public function setItemType($itemType)
    {
        if (! in_array($itemType, [self::TYPE_PRODUCT, self::TYPE_SERVICE]) ) {
            throw new \InvalidArgumentException('Item type [' . $itemType . '] not is allowed');
        }
        $this->itemType = $itemType;
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
     * @param array $data
     */
    abstract public function toTransform(array $data);
    
    /**
     * @return array
     */
    abstract public function getData();
    
}
?>