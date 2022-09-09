<?php
namespace Gesfrota\Model\Domain;

use Gesfrota\Model\AbstractActivable;

/**
 * Prestador de Serviço
 * @Entity
 * @Table(name="service_providers")
 */
class ServiceProvider extends AbstractActivable {
    
    /**
     * Serviço de Abastecimento
     * @var string
     */
    const SERVICE_SUPPLY = 'S';
    
    /**
     * Serviço de Manutenção
     * @var string
     */
    const SERVICE_MAINTENANCE = 'M';

	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $alias;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $nif;
	
	/**
	 * @Column(type="simple_array")
	 * @var array
	 */
	protected $services;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $contact;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $phone;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $email;
	
		
	/**
	 * Construtor
	 */
	public function __construct() {
		parent::__construct();
		$this->services = [];
	}
	
	/**
	 * Obtem $name
	 *
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * Obtem $alias
	 *
	 * @return string
	 */
	public function getAlias() {
		return $this->alias;
	}
	
	/**
	 * Obtem $nif
	 *
	 * @return string
	 */
	public function getNif() {
		return $this->nif;
	}

	/**
	 * Obtem $services
	 *
	 * @return array
	 */
	public function getServices() {
		return $this->services;
	}
	
	/**
	 * Obtem $contact
	 *
	 * @return string
	 */
	public function getContact() {
	    return $this->contact;
	}
	
	/**
	 * Obtem $phone
	 *
	 * @return string
	 */
	public function getPhone() {
	    return $this->phone;
	}
	
	/**
	 * Obtem $email
	 *
	 * @return string
	 */
	public function getEmail() {
	    return $this->email;
	}
	
	/**
	 * Atribui $name
	 *
	 * @param string $name
	 */
	public function setName( $name ) {
		$this->name = $name;
	}
	
	/**
	 * Atribui $alias
	 *
	 * @param string $alias
	 */
	public function setAlias( $alias ) {
	    $this->alias = $alias;
	}
	
	/**
	 * Atribui $nif
	 *
	 * @param string $nif
	 */
	public function setNif( $nif ) {
		$this->nif = $nif;
	}

	/**
	 * Atribui $services
	 *
	 * @param array|string $services
	 * @exception \DomainException
	 */
	public function setServices( $services ) {
	    if ( ! is_array($services) ) {
	        $services = is_null($services) ? [] : [$services];
	    }
	    foreach ($services as $service) {
	        if (! self::isServiceAllowed($service)) {
	            throw new \DomainException($service . ' is not service allowed');
	        }
	    }
	    $this->services = $services;
	}
	
	/**
	 * Atribui $contact
	 *
	 * @param string $contact
	 */
	public function setContact( $contact ) {
	    $this->contact = $contact;
	}
	
	/**
	 * Atribui $phone
	 *
	 * @param string $phone
	 */
	public function setPhone( $phone ) {
	    $this->phone = $phone;
	}
	
	/**
	 * Atribui $email
	 *
	 * @param string $email
	 */
	public function setEmail( $email ) {
	    $this->email = $email;
	}
	
	/**
	 * Verifica se o serviço é permitido
	 * @param string $service
	 * @return bool
	 */
	public static function isServiceAllowed( $service ) {
	   return array_key_exists($service, self::getServicesAllowed());
	}
	
	/**
	 * Obtem a lista de serviços permitidos
	 * 
	 * @return string[]
	 */
	public static function getServicesAllowed()
	{
	    return [self::SERVICE_SUPPLY => 'Abastecimento',
	            self::SERVICE_MAINTENANCE => 'Manutenção'];
	}
	
	/**
	 * @return string
	 */
	public function __toString() {
		return $this->getAlias();
	}
}
?>