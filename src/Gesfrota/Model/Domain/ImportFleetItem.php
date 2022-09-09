<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\Entity;
use Doctrine\ORM\EntityManager;
use Gesfrota\Util\Format;

/**
 * Item de Frota Importado
 * 
 * @Entity
 * @Table(name="import_fleet_items")
 * @EntityListeners({"Gesfrota\Model\Listener\ImportItemListener"})
 */
class ImportFleetItem extends Entity {
    
    /**
	 * @Column(type="json_array")
	 * @var array
	 */
    protected $data;
    
    /**
     * @ManyToOne(targetEntity="ImportFleet", inversedBy="items")
     * @JoinColumn(name="import_id", referencedColumnName="id")
     * @var ImportFleet
     */
    protected $import;
    
    /**
     * @ManyToOne(targetEntity="FleetItem")
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
     * @param ImportFleet $import
     * @param array $data
     */
    public function __construct(ImportFleet $import, array $data) {
        parent::__construct();
        $this->import = $import;
        $this->data = $data;
    }
    
    /**
     * @return string
     */
    public function getAlias() {
        return $this->data[0] . ' ' . $this->data[3] . ' ' . $this->data[2];
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
     * @return FleetItem
     */
    public function getReference() {
        return $this->reference;
    }
    
    /**
     * @param FleetItem $reference
     */
    public function setReference(FleetItem $reference = null)  {
        $this->status = false;
        if ( $reference ) {
            $this->status = true;
            $this->reference = $reference;
        }
    }
    
    /**
     * @param EntityManager $em
     * @return boolean
     */
    public function toPreProcess(EntityManager $em) {
        if ( $this->getReference() ) {
            return true;
        }
        if ( $this->isVehicle() ) {
            $rep = $em->getRepository(Vehicle::getClass());
            $criteria = ['plate' => $this->getData()[0]];
        } else {
            $rep = $em->getRepository(Equipment::getClass());
            $criteria = ['serialNumber' => $this->getData()[6]];
        }
        if ($item = $rep->findOneBy($criteria) ) {
            $this->setReference($item);
            return true;
        }
        return false;
    }

    /**
     * @param EntityManager $em
     * @return FleetItem
     */
    public function toTransform(EntityManager $em = null) {
        $short = 15-count($this->import->getHeader());
        $expanded = ! $short > 0; 
        if ( $this->isVehicle() ) {
            
            // VEHICLE
            $item = new Vehicle();
            $item->setPlate($this->data[0]);
            if ($expanded ) {
                if ($em && !empty($this->data[1]) ) {
                    $model = $em->getRepository(VehicleModel::getClass())->findOneBy(['fipe' => $this->data[1]]);
                }
                $item->setAssetCode($this->data[2]);
            }
            if (!isset($model)) {
                $model = new VehicleModel($this->data[3-$short]);
                $model->setFamily(new VehicleFamily($this->data[4-$short]));
                $model->setMaker(new VehicleMaker($this->data[5-$short]));
            }
            $item->setModel($model);
            if ($value = $this->toFleet($this->data[6-$short])) {
                $item->setFleet($value);
            }
            $item->setRenavam($this->data[7-$short]);
            $item->setVin($this->data[8-$short]);
            if ($value = $this->toEngine($this->data[9-$short])) {
                $item->setEngine($value);
            }
            $item->setYear($this->data[10-$short], $this->data[11-$short]);
            $item->setOdometer( $this->data[12-$short]);
            if (!empty($this->data[13-$short]) && $em) {
                $nif = strlen($this->data[13-$short]) == 14 ? Format::CPF($this->data[13-$short]) : Format::CNPJ($this->data[13-$short]);
                $owner = $em->getRepository(Owner::getClass())->findOneBy(['nif' => $nif]);
                if ($owner == null && !empty($this->data[14-$short])) {
                    $owner = strlen($this->data[13-$short]) == 14 ? new OwnerPerson() : new OwnerCompany();
                    $owner->setNif($nif);
                    $owner->setName($this->data[14-$short]);
                }
                $item->setOwner($owner);
            }
        } else {
            
            // EQUIPAMENT
            $item = new Equipment();
            if ($expanded) {
                $item->setAssetCode($this->data[2]);
            }
            $item->setDescription($this->data[3-$short]);
            if ($value = $this->toFleet($this->data[6-$short])) {
                $item->setFleet($value);
            }
            $item->setSerialNumber($this->data[8-$short]);
            if ($value = $this->toEngine($this->data[9-$short])) {
                $item->setEngine($value);
            }
        }
        $item->setResponsibleUnit($this->import->getAgency());
        return $item;
    }
    
    /**
     * 
     * @param string $value
     * @return integer
     */
    private function toEngine($value) {
        switch (strtoupper($value)) {
            case 'GASOLINA':
                return Engine::GASOLINE;
                
            case 'ETANOL':
                return Engine::ETHANOL;
                
            case 'FLEX':
                return Engine::FLEX;
                
            case 'DIESEL':
                return Engine::DIESEL;
        }
    }
    
    /**
     * @param string $value
     * @return integer
     */
    private function toFleet($value) {
        switch (str_replace('Ó', 'O', strtoupper(substr($value, 0, -1)))) {
            case 'PROPRI':
                return Fleet::OWN;
                
            case 'LOCAD':
                return Fleet::RENTED;
                
            case 'CEDID':
                return Fleet::ASSIGNED;
                
            case 'ACAUTELAD':
                return Fleet::GUARDED;
        }
    }

    
    /**
     * @return boolean
     */
    public function isVehicle() {
        $short = 15-count($this->import->getHeader());
        return stripos($this->data[4-$short], 'EQUIPAMENTO') === false;
    }
    
}