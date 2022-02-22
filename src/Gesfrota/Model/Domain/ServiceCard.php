<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\Entity;

/**
 * Cartão de Serviço
 * @Entity
 * @Table(name="service_cards")
 */
class ServiceCard extends Entity {
    
    /**
     * @Column(type="string")
     * @var string
     */
    protected $number;
    
    /**
     * @ManyToOne(targetEntity="ServiceProvider")
     * @JoinColumn(name="service_provider_id", referencedColumnName="id")
     * @var ServiceProvider
     */
    protected $serviceProvider;
    
    /**
     * @ManyToOne(targetEntity="FleetItem", inversedBy="cards")
     * @JoinColumn(name="fleet_item_id", referencedColumnName="id")
     * @var FleetItem
     */
    protected $fleetItem;
    
    /**
     * @param string $number
     * @param ServiceProvider $provider
     * @param FleetItem $item
     */
    public function __construct($number = null, ServiceProvider $provider = null, FleetItem $item = null) {
    	parent::__construct();
    	if (! ($number === null)) {
    		$this->setNumber($number);
    	}
    	if (! ($provider === null)) {
    		$this->setServiceProvider($provider);
    	}
    	if (! ($item === null)) {
    		$this->setFleetItem($item);
    	}
    }
    
    /**
     * {@inheritDoc}
     * @see Entity::getCode()
     */
    public function getCode() {
    	return $this->getNumber();
    }
    
    /**
     * @return string
     */
    public function getNumber() {
        return $this->number;
    }

    /**
     * @return ServiceProvider
     */
    public function getServiceProvider() {
        return $this->serviceProvider;
    }

    /**
     * @return FleetItem
     */
    public function getFleetItem() {
        return $this->fleetItem;
    }

    /**
     * @param string $number
     */
    public function setNumber($number) {
        $this->number = $number;
    }

    /**
     * @param ServiceProvider $provider
     */
    public function setServiceProvider(ServiceProvider $provider) {
        $this->serviceProvider = $provider;
    }

    /**
     * @param FleetItem $item
     */
    public function setFleetItem(FleetItem $item)  {
        $this->fleetItem = $item;
    }

}