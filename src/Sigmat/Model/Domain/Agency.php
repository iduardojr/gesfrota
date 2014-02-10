<?php
namespace Sigmat\Model\Domain;

use Sigmat\Model\AbstractActivable;

/**
 * Orgão
 * @Entity
 * @Table(name="agencies")
 */
class Agency extends AbstractActivable {

	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $acronym;
	
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
	 * Obtem $acronym
	 *
	 * @return string
	 */
	public function getAcronym() {
		return $this->acronym;
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
	 * Atribui $acronym
	 *
	 * @param string $acronym
	 */
	public function setAcronym( $acronym ) {
		$this->acronym = $acronym;
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
	 * @return string
	 */
	public function __toString() {
		return $this->getAcronym();
	}
}
?>