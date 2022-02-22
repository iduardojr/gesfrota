<?php

namespace DoctrineProxies\__CG__\Gesfrota\Model\Domain;


/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Vehicle extends \Gesfrota\Model\Domain\Vehicle implements \Doctrine\ORM\Proxy\Proxy
{
    /**
     * @var \Closure the callback responsible for loading properties in the proxy object. This callback is called with
     *      three parameters, being respectively the proxy object to be initialized, the method that triggered the
     *      initialization process and an array of ordered parameters that were passed to that method.
     *
     * @see \Doctrine\Common\Proxy\Proxy::__setInitializer
     */
    public $__initializer__;

    /**
     * @var \Closure the callback responsible of loading properties that need to be copied in the cloned object
     *
     * @see \Doctrine\Common\Proxy\Proxy::__setCloner
     */
    public $__cloner__;

    /**
     * @var boolean flag indicating if this object was already initialized
     *
     * @see \Doctrine\Persistence\Proxy::__isInitialized
     */
    public $__isInitialized__ = false;

    /**
     * @var array<string, null> properties to be lazy loaded, indexed by property name
     */
    public static $lazyPropertiesNames = array (
);

    /**
     * @var array<string, mixed> default values of properties to be lazy loaded, with keys being the property names
     *
     * @see \Doctrine\Common\Proxy\Proxy::__getLazyProperties
     */
    public static $lazyPropertiesDefaults = array (
);



    public function __construct(?\Closure $initializer = null, ?\Closure $cloner = null)
    {

        $this->__initializer__ = $initializer;
        $this->__cloner__      = $cloner;
    }

    /**
     * {@inheritDoc}
     * @param string $name
     */
    public function __get($name)
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__get', [$name]);
        return parent::__get($name);
    }

    /**
     * {@inheritDoc}
     * @param string $name
     * @param mixed  $value
     */
    public function __set($name, $value)
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__set', [$name, $value]);
        return parent::__set($name, $value);
    }



    /**
     * 
     * @return array
     */
    public function __sleep()
    {
        if ($this->__isInitialized__) {
            return ['__isInitialized__', 'plate', 'yearModel', 'yearManufacture', 'vin', 'renavam', 'model', 'owner', 'odometer', 'engine', 'assetCode', 'fleet', 'responsibleUnit', 'cards', 'createdAt', 'updatedAt', 'active', 'id'];
        }

        return ['__isInitialized__', 'plate', 'yearModel', 'yearManufacture', 'vin', 'renavam', 'model', 'owner', 'odometer', 'engine', 'assetCode', 'fleet', 'responsibleUnit', 'cards', 'createdAt', 'updatedAt', 'active', 'id'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Vehicle $proxy) {
                $proxy->__setInitializer(null);
                $proxy->__setCloner(null);

                $existingProperties = get_object_vars($proxy);

                foreach ($proxy::$lazyPropertiesDefaults as $property => $defaultValue) {
                    if ( ! array_key_exists($property, $existingProperties)) {
                        $proxy->$property = $defaultValue;
                    }
                }
            };

        }
    }

    /**
     * 
     */
    public function __clone()
    {
        $this->__cloner__ && $this->__cloner__->__invoke($this, '__clone', []);
    }

    /**
     * Forces initialization of the proxy
     */
    public function __load()
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized()
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized)
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null)
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer()
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null)
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner()
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @deprecated no longer in use - generated code now relies on internal components rather than generated public API
     * @static
     */
    public function __getLazyProperties()
    {
        return self::$lazyPropertiesDefaults;
    }

    
    /**
     * {@inheritDoc}
     */
    public function getOwner()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getOwner', []);

        return parent::getOwner();
    }

    /**
     * {@inheritDoc}
     */
    public function setOwner(\Gesfrota\Model\Domain\Owner $owner = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setOwner', [$owner]);

        return parent::setOwner($owner);
    }

    /**
     * {@inheritDoc}
     */
    public function getDescription()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDescription', []);

        return parent::getDescription();
    }

    /**
     * {@inheritDoc}
     */
    public function getPlate()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPlate', []);

        return parent::getPlate();
    }

    /**
     * {@inheritDoc}
     */
    public function getCode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCode', []);

        return parent::getCode();
    }

    /**
     * {@inheritDoc}
     */
    public function getYearModel()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getYearModel', []);

        return parent::getYearModel();
    }

    /**
     * {@inheritDoc}
     */
    public function getYearManufacture()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getYearManufacture', []);

        return parent::getYearManufacture();
    }

    /**
     * {@inheritDoc}
     */
    public function getVin()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getVin', []);

        return parent::getVin();
    }

    /**
     * {@inheritDoc}
     */
    public function getRenavam()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRenavam', []);

        return parent::getRenavam();
    }

    /**
     * {@inheritDoc}
     */
    public function getModel()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getModel', []);

        return parent::getModel();
    }

    /**
     * {@inheritDoc}
     */
    public function getOdometer()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getOdometer', []);

        return parent::getOdometer();
    }

    /**
     * {@inheritDoc}
     */
    public function setOdometer($odometer)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setOdometer', [$odometer]);

        return parent::setOdometer($odometer);
    }

    /**
     * {@inheritDoc}
     */
    public function setPlate($plate)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPlate', [$plate]);

        return parent::setPlate($plate);
    }

    /**
     * {@inheritDoc}
     */
    public function setYearModel(int $year)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setYearModel', [$year]);

        return parent::setYearModel($year);
    }

    /**
     * {@inheritDoc}
     */
    public function setYearManufacture(int $year)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setYearManufacture', [$year]);

        return parent::setYearManufacture($year);
    }

    /**
     * {@inheritDoc}
     */
    public function setYear(int $manufacture, int $model = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setYear', [$manufacture, $model]);

        return parent::setYear($manufacture, $model);
    }

    /**
     * {@inheritDoc}
     */
    public function getYear(bool $simple = true)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getYear', [$simple]);

        return parent::getYear($simple);
    }

    /**
     * {@inheritDoc}
     */
    public function setVin($vin)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setVin', [$vin]);

        return parent::setVin($vin);
    }

    /**
     * {@inheritDoc}
     */
    public function setRenavam(int $renavam)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRenavam', [$renavam]);

        return parent::setRenavam($renavam);
    }

    /**
     * {@inheritDoc}
     */
    public function setModel(\Gesfrota\Model\Domain\VehicleModel $model)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setModel', [$model]);

        return parent::setModel($model);
    }

    /**
     * {@inheritDoc}
     */
    public function getEngine()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEngine', []);

        return parent::getEngine();
    }

    /**
     * {@inheritDoc}
     */
    public function getFleet()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFleet', []);

        return parent::getFleet();
    }

    /**
     * {@inheritDoc}
     */
    public function getResponsibleUnit()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getResponsibleUnit', []);

        return parent::getResponsibleUnit();
    }

    /**
     * {@inheritDoc}
     */
    public function getCreatedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCreatedAt', []);

        return parent::getCreatedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function getUpdatedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUpdatedAt', []);

        return parent::getUpdatedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function getAssetCode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAssetCode', []);

        return parent::getAssetCode();
    }

    /**
     * {@inheritDoc}
     */
    public function setAssetCode($assetCode)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setAssetCode', [$assetCode]);

        return parent::setAssetCode($assetCode);
    }

    /**
     * {@inheritDoc}
     */
    public function setResponsibleUnit(\Gesfrota\Model\Domain\Agency $unit)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setResponsibleUnit', [$unit]);

        return parent::setResponsibleUnit($unit);
    }

    /**
     * {@inheritDoc}
     */
    public function getFleetType()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFleetType', []);

        return parent::getFleetType();
    }

    /**
     * {@inheritDoc}
     */
    public function setFleet(int $fleet)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setFleet', [$fleet]);

        return parent::setFleet($fleet);
    }

    /**
     * {@inheritDoc}
     */
    public function setEngine(int $engine)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEngine', [$engine]);

        return parent::setEngine($engine);
    }

    /**
     * {@inheritDoc}
     */
    public function setUpdated()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setUpdated', []);

        return parent::setUpdated();
    }

    /**
     * {@inheritDoc}
     */
    public function addCard(\Gesfrota\Model\Domain\ServiceCard $card)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addCard', [$card]);

        return parent::addCard($card);
    }

    /**
     * {@inheritDoc}
     */
    public function removeCard($card)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeCard', [$card]);

        return parent::removeCard($card);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllCards()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAllCards', []);

        return parent::getAllCards();
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, '__toString', []);

        return parent::__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function setActive($active)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setActive', [$active]);

        return parent::setActive($active);
    }

    /**
     * {@inheritDoc}
     */
    public function getActive()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getActive', []);

        return parent::getActive();
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getId', []);

        return parent::getId();
    }

    /**
     * {@inheritDoc}
     */
    public function toArray()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toArray', []);

        return parent::toArray();
    }

}