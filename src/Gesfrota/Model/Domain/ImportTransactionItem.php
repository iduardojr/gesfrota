<?php
namespace Gesfrota\Model\Domain;

/**
 * Item de uma transação
 * 
 * @MappedSuperclass
 */
abstract class ImportTransactionItem {
    
    /**
     * @Id
     * @Column(name="transaction_id", type="integer")
     * @GeneratedValue
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
     * @ManyToOne(targetEntity="ImportSupply", inversedBy="items")
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
     * @param ImportTransaction $import
     * @param array $data
     */
    public function __construct(ImportTransaction $import, array $data) {
        $this->setTransactionImport($import);
        $this->toTransform($data);
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
     * @param array $data
     */
    public function toTransform(array $data) 
    {
        $this->setTransactionId((int) $data[0]);
        $this->setTransactionCostCenter($data[1]);
        $this->setTransactionDate(new \DateTime($data[2]));
        $this->setVehiclePlate($data[3]);
        $this->setVehicleDescription($data[4]);
    }
    
    /**
     * @return array
     */
    public function getData()
    {
        $data = [];
        $data[0] = $this->getTransactionId();
        $data[1] = $this->getTransactionAgency() . '<' . $this->getTransactionCostCenter() . '>';
        $data[2] = $this->getTransactionDate()->format('d/m/Y H:i:s');
        $data[3] = $this->getVehiclePlate();
        $data[4] = $this->getTransactionVehicle() ? $this->getTransactionVehicle()->getDescription() : $this->getVehicleDescription();
        return $data;
    }
}
?>