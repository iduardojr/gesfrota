<?php
namespace Gesfrota\Model\Sys;

use Gesfrota\Model\Entity;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\FleetItem;

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
    public function getGroupBy() {
        return $this->groupBy;
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
     * @param FleetItem $reference
     */
    public function setReference(FleetItem $reference)  {
        $this->reference = $reference;
    }
    
}