<?php

namespace DoctrineProxies\__CG__\Gesfrota\Model\Domain;


/**
 * DO NOT EDIT THIS FILE - IT WAS CREATED BY DOCTRINE'S PROXY GENERATOR
 */
class Driver extends \Gesfrota\Model\Domain\Driver implements \Doctrine\ORM\Proxy\Proxy
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
            return ['__isInitialized__', 'license', 'renach', 'vehicles', 'expires', 'name', 'nif', 'gender', 'birthday', 'email', 'cell', 'password', 'changePassword', 'lotation', 'active', 'id'];
        }

        return ['__isInitialized__', 'license', 'renach', 'vehicles', 'expires', 'name', 'nif', 'gender', 'birthday', 'email', 'cell', 'password', 'changePassword', 'lotation', 'active', 'id'];
    }

    /**
     * 
     */
    public function __wakeup()
    {
        if ( ! $this->__isInitialized__) {
            $this->__initializer__ = function (Driver $proxy) {
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
    public function getLicense()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLicense', []);

        return parent::getLicense();
    }

    /**
     * {@inheritDoc}
     */
    public function getRenach()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getRenach', []);

        return parent::getRenach();
    }

    /**
     * {@inheritDoc}
     */
    public function getVehicles()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getVehicles', []);

        return parent::getVehicles();
    }

    /**
     * {@inheritDoc}
     */
    public function getExpires()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getExpires', []);

        return parent::getExpires();
    }

    /**
     * {@inheritDoc}
     */
    public function setLicense(int $license)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLicense', [$license]);

        return parent::setLicense($license);
    }

    /**
     * {@inheritDoc}
     */
    public function setRenach($renach)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setRenach', [$renach]);

        return parent::setRenach($renach);
    }

    /**
     * {@inheritDoc}
     */
    public function setVehicles(array $licenses = NULL)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setVehicles', [$licenses]);

        return parent::setVehicles($licenses);
    }

    /**
     * {@inheritDoc}
     */
    public function setExpires(\DateTime $expires)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setExpires', [$expires]);

        return parent::setExpires($expires);
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getName', []);

        return parent::getName();
    }

    /**
     * {@inheritDoc}
     */
    public function getFirstName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getFirstName', []);

        return parent::getFirstName();
    }

    /**
     * {@inheritDoc}
     */
    public function getLastName()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLastName', []);

        return parent::getLastName();
    }

    /**
     * {@inheritDoc}
     */
    public function getNif()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getNif', []);

        return parent::getNif();
    }

    /**
     * {@inheritDoc}
     */
    public function getGender()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getGender', []);

        return parent::getGender();
    }

    /**
     * {@inheritDoc}
     */
    public function getBirthday()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getBirthday', []);

        return parent::getBirthday();
    }

    /**
     * {@inheritDoc}
     */
    public function getEmail()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getEmail', []);

        return parent::getEmail();
    }

    /**
     * {@inheritDoc}
     */
    public function getCell()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getCell', []);

        return parent::getCell();
    }

    /**
     * {@inheritDoc}
     */
    public function getPassword()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getPassword', []);

        return parent::getPassword();
    }

    /**
     * {@inheritDoc}
     */
    public function getLotation()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getLotation', []);

        return parent::getLotation();
    }

    /**
     * {@inheritDoc}
     */
    public function isChangePassword()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'isChangePassword', []);

        return parent::isChangePassword();
    }

    /**
     * {@inheritDoc}
     */
    public function getUserType()
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'getUserType', []);

        return parent::getUserType();
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setName', [$name]);

        return parent::setName($name);
    }

    /**
     * {@inheritDoc}
     */
    public function setNif($nif)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setNif', [$nif]);

        return parent::setNif($nif);
    }

    /**
     * {@inheritDoc}
     */
    public function setGender($gender)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setGender', [$gender]);

        return parent::setGender($gender);
    }

    /**
     * {@inheritDoc}
     */
    public function setBirthday(\DateTime $birthday)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setBirthday', [$birthday]);

        return parent::setBirthday($birthday);
    }

    /**
     * {@inheritDoc}
     */
    public function setEmail($email)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setEmail', [$email]);

        return parent::setEmail($email);
    }

    /**
     * {@inheritDoc}
     */
    public function setCell($cell)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setCell', [$cell]);

        return parent::setCell($cell);
    }

    /**
     * {@inheritDoc}
     */
    public function setPassword($password)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setPassword', [$password]);

        return parent::setPassword($password);
    }

    /**
     * {@inheritDoc}
     */
    public function setLotation(\Gesfrota\Model\Domain\AdministrativeUnit $unit)
    {

        $this->__initializer__ && $this->__initializer__->__invoke($this, 'setLotation', [$unit]);

        return parent::setLotation($unit);
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
