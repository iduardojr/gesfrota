<?php
namespace Gesfrota\Model\Domain;

/**
 * Veículo
 * @Entity
 * @Table(name="vehicles")
 */
class Vehicle extends FleetItem {
	
	/**
	 * @var string
	 */
	const FLEET_TYPE = 'Veículo';
    
    /**
     * @Column(type="string")
     * @var string
     */
    protected $plate;
    
    /**
     * @Column(name="year_model", type="integer")
     * @var integer
     */
    protected $yearModel;
    
    /**
     * @Column(name="year_manufacture", type="integer")
     * @var integer
     */
    protected $yearManufacture;
    
    /**
     * @Column(type="string")
     * @var string
     */
    protected $vin;
    
    /**
     * @Column(type="integer")
     * @var integer
     */
    protected $renavam;
    
    /**
     * @ManyToOne(targetEntity="VehicleModel")
     * @JoinColumn(name="vehicle_model_id", referencedColumnName="id")
     * @var VehicleModel
     */
    protected $model;
    
    /**
     * @ManyToOne(targetEntity="Owner")
     * @JoinColumn(name="owner_id", referencedColumnName="id")
     * @var Owner
     */
    protected $owner;
    
    /**
     * @Column(type="integer")
     * @var integer
     */
    protected $odometer;
    
    
    /**
     * @param Agency $unit
     */
    public function __construct(Agency $unit = null) {
        parent::__construct($unit);
        if ( $unit ) {
       	 	$this->setOwner($unit->getOwner());
        }
    }
    
    /**
	 * @return Owner
	 */
	public function getOwner() {
		return $this->owner;
	}

	/**
	 * @param Owner $owner
	 */
	public function setOwner(Owner $owner = null) {
		$this->owner = $owner;
	}

	/**
     * {@inheritDoc}
     * @see \Gesfrota\Model\Domain\FleetItem::getDescription()
     */
    public function getDescription() {
        $descr = (string) $this->getModel();
        $descr.= ' ' . implode('/', $this->getYear());
        return $descr;
    }
    
    /**
     * @return string
     */
    public function getPlate() {
        return $this->plate;
    }
    
    /**
     * {@inheritDoc}
     * @see \Gesfrota\Model\Entity::getCode()
     */
    public function getCode() {
        return $this->getPlate();
    }

    /**
     * @return integer
     */
    public function getYearModel() {
        return $this->yearModel;
    }
    

    /**
     * @return integer
     */
    public function getYearManufacture() {
        return $this->yearManufacture;
    }

    /**
     * @return string
     */
    public function getVin() {
        return $this->vin;
    }

    /**
     * @return integer
     */
    public function getRenavam() {
        return $this->renavam;
    }

    /**
     * @return VehicleModel
     */
    public function getModel() {
        return $this->model;
    }

    /**
	 * @return integer
	 */
	public function getOdometer() {
		return $this->odometer;
	}

	/**
	 * @param integer $odometer
	 */
	public function setOdometer($odometer) {
		$this->odometer = $odometer;
	}

	/**
     * @param string $plate
     */
    public function setPlate($plate) {
        $this->plate = $plate;
    }

    /**
     * @param integer $year
     */
    public function setYearModel(int $year)   {
        $this->yearModel = $year;
    }

    /**
     * @param integer $year
     */
    public function setYearManufacture(int $year) {
        $this->yearManufacture = $year;
    }
    
    /**
     * Atribui ano do veículo
     * 
     * @param int $model
     * @param int $manufacture
     */
    public function setYear(int $manufacture, int $model = null) {
        $this->setYearManufacture($manufacture);
        if ($model <= 0) {
            $model = $manufacture;
        }
        $this->setYearModel($model);
    }
    
    /**
     * @param boolean $simple
     * @return integer[]
     */
    public function getYear(bool $simple = true) {
        if ($simple && $this->getYearModel() == $this->getYearManufacture()) {
            return  [$this->getYearModel()];
        }
        return [$this->getYearManufacture(), $this->getYearModel()];
    }
    

    /**
     * @param string $vin
     */
    public function setVin($vin) {
        $this->vin = $vin;
    }

    /**
     * @param integer $renavam
     */
    public function setRenavam(int $renavam) {
        $this->renavam = $renavam;
    }

    /**
     * @param VehicleModel $model
     */
    public function setModel(VehicleModel $model) {
        $this->model = $model;
    }
    
}
?>