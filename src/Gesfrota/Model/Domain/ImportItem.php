<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\Entity;
use Doctrine\ORM\EntityManager;
use Gesfrota\Util\Format;

/**
 * @Entity
 * @Table(name="import_items")
 * @EntityListeners({})
 */
class ImportItem extends Entity {
    
    /**
	 * @Column(type="json_array")
	 * @var array
	 */
    protected $data;
    
    /**
     * @ManyToOne(targetEntity="Import", inversedBy="items")
     * @JoinColumn(name="import_id", referencedColumnName="id")
     * @var Import
     */
    protected $import;
    
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
        if ( $this->isVehicle() ) {
            $item = new Vehicle();
            if ($this->data[1] && $em) {
                $model = $em->getRepository(VehicleModel::getClass())->findOneBy(['fipe' => $this->data[1]]);
            }
            if (!isset($model)) {
                $model = new VehicleModel($this->data[2], new VehicleMaker($this->data[3]), new VehicleFamily($this->data[4]));
                $model->setFipe($this->data[1]);
            }
            $item->setModel($model);
            $item->setPlate($this->data[0]);
            $item->setRenavam($this->data[5]);
            $item->setVin($this->data[6]);
            $item->setYear($this->data[8], $this->data[9]);
            $item->setOdometer((int) $this->data[10]);
            if ($this->data[13] && $em) {
                $nif = Format::CNPJ($this->data[13]);
                $owner = $em->getRepository(OwnerCompany::getClass())->findOneBy(['nif' => $nif]);
                if ($owner == null && $this->data[14]) {
                    $owner = new OwnerCompany();
                    $owner->setNif($nif);
                    $owner->setName($this->data[14]);
                }
                $item->setOwner($owner);
            }
        } else {
            $item = new Equipment();
            $item->setDescription($this->data[3]);
            $item->setSerialNumber($this->data[6]);
        }
        $item->setResponsibleUnit($this->import->getAgency());
        try {
            switch (strtoupper($this->data[7])) {
                case 'GASOLINA':
                    $item->setEngine(Engine::GASOLINE);
                    break;
                    
                case 'ETANOL':
                    $item->setEngine(Engine::ETHANOL);
                    break;
                    
                case 'FLEX':
                    $item->setEngine(Engine::FLEX);
                    break;
                    
                case 'DIESEL':
                    $item->setEngine(Engine::DIESEL);
                    break;
            }
        } catch (\DomainException $e) { }
        try {
            switch (str_replace('Ó', 'O', strtoupper(substr($this->data[11], 0, -1)))) {
                case 'PROPRI':
                    $item->setFleet(Fleet::OWN);
                    break;
                    
                case 'LOCAD':
                    $item->setFleet(Fleet::RENTED);
                    break;
                    
                case 'CEDID':
                    $item->setFleet(Fleet::ASSIGNED);
                    break;
                    
                case 'ACAUTELAD':
                    $item->setFleet(Fleet::GUARDED);
                    break;
            }
        } catch (\DomainException $e) { }
        $item->setAssetCode($this->data[12]);
        return $item;
    }

    
    /**
     * @return boolean
     */
    private function isVehicle() {
        return stripos($this->data[4], 'EQUIPAMENTO') === false;
    }
    
}