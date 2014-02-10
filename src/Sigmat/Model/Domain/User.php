<?php
namespace Sigmat\Model\Domain;

use Sigmat\Util\Crypt;
use Sigmat\Model\AbstractActivable;

/**
 * Usuário do sistema
 * 
 * @Entity 
 * @Table(name="user")
 */
class User extends AbstractActivable {
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $name;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $cpf;
	
	/**
	 * @Column(type="datetime")
	 * @var \DateTime
	 */
	protected $birthday;

	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $email;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $password;
	
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
	 * Obtem $cpf
	 *
	 * @return string
	 */
	public function getCpf() {
		return $this->cpf;
	}

	/**
	 * Obtem $birthday
	 *
	 * @return \DateTime
	 */
	public function getBirthday() {
		return $this->birthday;
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
	 * Obtem $password
	 *
	 * @return string
	 */
	public function getPassword() {
		return $this->password;
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
	 * Atribui $cpf
	 *
	 * @param string $cpf
	 */
	public function setCpf( $cpf ) {
		$this->cpf = $cpf;
	}

	/**
	 * Atribui $birthday
	 *
	 * @param DateTime $birthday
	 */
	public function setBirthday( $birthday ) {
		$this->birthday = $birthday;
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
	 * Atribui $password
	 *
	 * @param string $password
	 */
	public function setPassword( $password ) {
		$this->password =  Crypt::encode($password);
	}

}
?>