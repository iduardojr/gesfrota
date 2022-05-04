<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\AbstractActivable;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Item da Frota
 * @Entity
 * @Table(name="fleet_items")
 * @EntityListeners({"Gesfrota\Model\Listener\FleetItemListener", "Gesfrota\Model\Listener\LoggerListener"})
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"V" = "Vehicle", "E" = "Equipment"})
 */
abstract class FleetItem extends AbstractActivable {
    
    /**
     * @Column(type="integer")
     * @var integer
     */
    protected $engine;
    
    /**
     * @Column(name="asset_code", type="string")
     * @var string
     */
    protected $assetCode;
    
    /**
     * @Column(type="integer")
     * @var integer
     */
    protected $fleet;
    
    /**
     * @ManyToOne(targetEntity="Agency")
     * @JoinColumn(name="responsible_unit_id", referencedColumnName="id")
     * @var Agency
     */
    protected $responsibleUnit;
    
    /**
     * @OneToMany(targetEntity="ServiceCard", mappedBy="fleetItem", indexBy="id")
     * @var ArrayCollection
     */
    protected $cards;
    
    /**
     * @Column(name="created_at", type="datetime")
     * @var \DateTime
     */
    protected $createdAt;
    
    /**
     * @Column(name="updated_at", type="datetime")
     * @var \DateTime
     */
    protected $updatedAt;
    
    /**
     * @param Agency $unit
     */
    public function __construct(Agency $unit = null) {
    	if ($unit) {
      	  	$this->setResponsibleUnit($unit);
    	}
        $this->cards = new ArrayCollection();
        $this->createdAt = $this->updatedAt = new \DateTime();
        parent::__construct();
    }
    
    /**
     * Obtem uma descrição
     * @return string
     */
    abstract public function getDescription();
    
    /**
     * Obtem $engine
     * @return integer
     */
    public function getEngine() {
        return $this->engine;
    }
    
    /**
     * @return integer
     */
    public function getFleet() {
        return $this->fleet;
    }

    /**
     * @return Agency
     */
    public function getResponsibleUnit() {
        return $this->responsibleUnit;
    }
    
    /**
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
    }
    
    /**
     * @return string
     */
    public function getAssetCode() {
    	return $this->assetCode;
    }
    
    /**
     * @param string $assetCode
     */
    public function setAssetCode($assetCode) {
    	$this->assetCode = $assetCode;
    }

    /**
     * @param Agency $responsibleUnit
     */
    public function setResponsibleUnit(Agency $unit) {
        $this->responsibleUnit = $unit;
    }
    
    /**
     * @return string
     */
    public function getFleetType() {
    	return constant(get_class($this) . '::FLEET_TYPE');
    }

    /**
     * @param integer $fleet
     * @throws \DomainException
     */
    public function setFleet(int $fleet) {
        if (!self::isFleetAllowed($fleet)) {
            throw new \DomainException('The ' . $fleet . ' is not fleet type allowed.');
        }
        $this->fleet = $fleet;
    }

    /**
     * Atribui $engine
     * 
     * @param integer $engine
     * @throws \DomainException
     */
    public function setEngine (int $engine) {
        if (!self::isEngineAllowed($engine)) {
            throw new \DomainException('The ' . $engine . ' is not engine type allowed.');
        }
        $this->engine = $engine;
    }
    
    public function setUpdated() {
        $this->updatedAt = new \DateTime();
    }
    
    /**
     * @param ServiceCard $card
     * @return bool
     */
    public function addCard(ServiceCard $card) {
        $card->setFleetItem($this);
        return $this->cards->add($card);
    }
    
    /**
     * @param integer|ServiceCard $card
     * @return false|ServiceCard
     */
    public function removeCard($card) {
        if ($card instanceof ServiceCard) {
           return $this->cards->removeElement($card);
        } else {
           return $this->cards->remove($card);
        }
    }
    
    /**
     * @return array
     */
    public function getAllCards() {
        return $this->cards->toArray();
    }
    
    /**
     * Verifica se o tipo de motor é permitido
     * @param integer $engine
     * @return bool
     */
    public static function isEngineAllowed( int $engine ) {
        return array_key_exists($engine, self::getEnginesAllowed());
    }
    
    /**
     * Verifica se o tipo de frota é permitido
     * @param integer $fleet
     * @return bool
     */
    public static function isFleetAllowed( int $fleet ) {
        return array_key_exists($fleet, self::getFleetAllowed());
    }
    
    /**
     * Obtem a lista de motores permitidos
     *
     * @return string[]
     */
    public static function getEnginesAllowed()
    {
        return [Engine::GASOLINE => 'Gasolina',
                Engine::ETHANOL => 'Etanol',
                Engine::FLEX => 'Flex',
                Engine::DIESEL => 'Diesel'
        ];
    }
    
    /**
     * Obtem a lista de frotas permitidas
     *
     * @return string[]
     */
    public static function getFleetAllowed()
    {
        return [Fleet::OWN => 'Própria',
                Fleet::RENTED => 'Locada',
                Fleet::ASSIGNED => 'Cedida',
                Fleet::GUARDED => 'Acautelada'
        ];
    }
    
    /**
     * @return string
     */
    public function __toString() {
        return $this->getDescription();
    }
 
    
}
?>