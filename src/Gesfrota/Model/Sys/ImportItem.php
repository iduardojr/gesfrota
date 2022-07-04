<?php
namespace Gesfrota\Model\Sys;

use Gesfrota\Model\Entity;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\FleetItem;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\Model\Domain\Equipment;
use Gesfrota\Model\Domain\Engine;
use Gesfrota\Model\Domain\Fleet;
use Gesfrota\Model\Domain\VehicleModel;
use Gesfrota\Model\Domain\VehicleMaker;
use Gesfrota\Model\Domain\VehicleFamily;

/**
 * @Entity
 * @Table(name="import_items")
 */
class ImportItem extends Entity {
    
    /**
	 * @Column(type="json_array")
	 * @var array
	 */
    protected $data;
    
    /**
     * @Column(name="group_by", type="string")
     * @var string
     */
    protected $groupBy;
    
    /**
     * @ManyToOne(targetEntity="Import", inversedBy="items")
     * @JoinColumn(name="import_id", referencedColumnName="id")
     * @var Import
     */
    protected $import;
    
     /**
     * @ManyToOne(targetEntity="Gesfrota\Model\Domain\Agency")
     * @JoinColumn(name="agency_id", referencedColumnName="id")
     * @var Agency
     */
    protected $agency;
    
    /**
     * @ManyToOne(targetEntity="Gesfrota\Model\Domain\FleetItem")
     * @JoinColumn(name="fleet_item_id", referencedColumnName="id")
     * @var FleetItem
     */
    protected $reference;
    
    /**
     * @Column(type="boolean")
     * @var boolean
     */
    protected $status;
    
    /**
     * @param Import $import
     * @param array $data
     */
    public function __construct(Import $import, array $data) {
        parent::__construct();
        $this->import = $import;
        $this->groupBy = $data[0];
        $this->data = $data;
    }
    
    /**
     * @return string
     */
    public function getAlias() {
        if ($this->isVehicle() ) {
            return $this->data[1] . ' ' . $this->data[2] . ' ' . $this->data[3];
        } 
        return $this->data[1] . ' ' . $this->data[3];
    }
    
    /**
     * @return string
     */
    public function getGroupBy() {
        return $this->groupBy;
    }
    
    /**
     * @return boolean
     */
    public function isVehicle() {
        return $this->data[4] != 'EQUIPAMENTOS';
    }
    
    /**
     * @return array
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @return Import
     */
    public function getImport() {
        return $this->import;
    }
    
    /**
     * @return boolean|null
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return Agency
     */
    public function getAgency() {
        return $this->agency;
    }

    /**
     * @return FleetItem
     */
    public function getReference() {
        return $this->reference;
    }

    /**
     * @param Agency $agency
     */
    public function setAgency(Agency $agency) {
        $this->agency = $agency;
    }
    
    /**
     * return FleetItem
     */
    public function toTransform() {
        if ( $this->isVehicle() ) {
            $new = new Vehicle($this->agency);
            $new->setModel(new VehicleModel($this->data[3], new VehicleMaker($this->data[2]), new VehicleFamily($this->data[4])));
            $new->setPlate($this->data[1]);
            $new->setRenavam($this->data[5]);
            $new->setVin($this->data[6]);
            $new->setYear($this->data[8], $this->data[9]);
            $new->setOdometer($this->data[10]);
        } else {
            $new = new Equipment($this->agency);
            $new->setDescription($this->data[3]);
            $new->setAssetCode($this->data[6]);
        }
        try {
            switch ($this->data[7]) {
                case 'GASOLINA':
                    $new->setEngine(Engine::GASOLINE);
                    break;
                    
                case 'ETANOL':
                    $new->setEngine(Engine::ETHANOL);
                    break;
                    
                case 'FLEX':
                    $new->setEngine(Engine::FLEX);
                    break;
                    
                case 'DIESEL':
                    $new->setEngine(Engine::DIESEL);
                    break;
            }
        } catch (\DomainException $e) { }
        try {
            switch ($this->data[11]) {
                case 'PROPRIO':
                    $new->setFleet(Fleet::OWN);
                    break;
                    
                case 'ALUGADO':
                    $new->setFleet(Fleet::RENTED);
                    break;
                    
                case 'CEDIDA':
                    $new->setFleet(Fleet::ASSIGNED);
                    break;
                    
                case 'ACAUTELADA':
                    $new->setFleet(Fleet::GUARDED);
                    break;
            }
        } catch (\DomainException $e) { }
        $this->setReference($new);
        return $new;
    }

    /**
     * @param FleetItem $reference
     */
    public function setReference(FleetItem $reference = null)  {
        $this->status = false;
        if ( $reference ) {
            $this->status = true;
            $this->reference = $reference;
            $this->agency = $reference->getResponsibleUnit();
        }
    }
    
}