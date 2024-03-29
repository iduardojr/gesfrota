<?php

namespace DoctrineProxies\__CG__\Gesfrota\Model\Domain;


/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Disposal extends \Gesfrota\Model\Domain\Disposal implements \Doctrine\ORM\Proxy\Proxy
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
            return ['__isInitialized__', 'description', 'status', 'justify', 'assets', 'requesterUnit', 'requestedBy', 'confirmedBy', 'declinedBy', 'requestedAt', 'confirmedAt', 'declinedAt', 'id'];
        }

        return ['__isInitialized__', 'description', 'status', 'justify', 'assets', 'requesterUnit', 'requestedBy', 'confirmedBy', 'declinedBy', 'requestedAt', 'confirmedAt', 'declinedAt', 'id'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Disposal $proxy) {
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
    public function __load(): void
    {
        $this->__initializer__ && $this->__initializer__->__invoke($this, '__load', []);
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __isInitialized(): bool
    {
        return $this->__isInitialized__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitialized($initialized): void
    {
        $this->__isInitialized__ = $initialized;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setInitializer(\Closure $initializer = null): void
    {
        $this->__initializer__ = $initializer;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __getInitializer(): ?\Closure
    {
        return $this->__initializer__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     */
    public function __setCloner(\Closure $cloner = null): void
    {
        $this->__cloner__ = $cloner;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific cloning logic
     */
    public function __getCloner(): ?\Closure
    {
        return $this->__cloner__;
    }

    /**
     * {@inheritDoc}
     * @internal generated method: use only when explicitly handling proxy specific loading logic
     * @deprecated no longer in use - generated code now relies on internal components rather than generated public API
     * @static
     */
    public function __getLazyProperties(): array
    {
        return self::$lazyPropertiesDefaults;
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
    public function setDescription($description)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setDescription', [$description]);

        return parent::setDescription($description);
    }

    /**
     * {@inheritDoc}
     */
    public function getJustify()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getJustify', []);

        return parent::getJustify();
    }

    /**
     * {@inheritDoc}
     */
    public function getStatus()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getStatus', []);

        return parent::getStatus();
    }

    /**
     * {@inheritDoc}
     */
    public function setStatus($status)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setStatus', [$status]);

        return parent::setStatus($status);
    }

    /**
     * {@inheritDoc}
     */
    public function addAsset(\Gesfrota\Model\Domain\DisposalItem $item)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'addAsset', [$item]);

        return parent::addAsset($item);
    }

    /**
     * {@inheritDoc}
     */
    public function removeAsset($item)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'removeAsset', [$item]);

        return parent::removeAsset($item);
    }

    /**
     * {@inheritDoc}
     */
    public function getAllAssets()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getAllAssets', []);

        return parent::getAllAssets();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalAssets()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTotalAssets', []);

        return parent::getTotalAssets();
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalAssetsValued()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getTotalAssetsValued', []);

        return parent::getTotalAssetsValued();
    }

    /**
     * {@inheritDoc}
     */
    public function getRequesterUnit()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRequesterUnit', []);

        return parent::getRequesterUnit();
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestedBy()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRequestedBy', []);

        return parent::getRequestedBy();
    }

    /**
     * {@inheritDoc}
     */
    public function getConfirmedBy()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getConfirmedBy', []);

        return parent::getConfirmedBy();
    }

    /**
     * {@inheritDoc}
     */
    public function getDeclinedBy()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDeclinedBy', []);

        return parent::getDeclinedBy();
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRequestedAt', []);

        return parent::getRequestedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function getConfirmedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getConfirmedAt', []);

        return parent::getConfirmedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function getDeclinedAt()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getDeclinedAt', []);

        return parent::getDeclinedAt();
    }

    /**
     * {@inheritDoc}
     */
    public function toRequest(\Gesfrota\Model\Domain\User $user)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toRequest', [$user]);

        return parent::toRequest($user);
    }

    /**
     * {@inheritDoc}
     */
    public function toConfirm(\Gesfrota\Model\Domain\User $user)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toConfirm', [$user]);

        return parent::toConfirm($user);
    }

    /**
     * {@inheritDoc}
     */
    public function toDecline(\Gesfrota\Model\Domain\User $user, $justify)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toDecline', [$user, $justify]);

        return parent::toDecline($user, $justify);
    }

    /**
     * {@inheritDoc}
     */
    public function toDevolve()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'toDevolve', []);

        return parent::toDevolve();
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
    public function getCode()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCode', []);

        return parent::getCode();
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
