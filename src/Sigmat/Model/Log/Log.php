<?php
namespace Sigmat\Model\Log;

use Doctrine\Common\Collections\ArrayCollection;
use Sigmat\Model\Entity;

/**
 * Evento de Log
 * 
 * @Entity 
 * @Table(name="logs")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="event", type="string")
 * @DiscriminatorMap({"Access" = "LogAccess", "Application" = "LogApplication"})
 */
abstract class Log extends Entity {
	
	// Severidade
	const Critical = 400;
	const Error = 300;
	const Warning = 200;
	const Notice = 100;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $description;
	
	/**
	 * @OneToMany(targetEntity="LogContext", mappedBy="log")
	 * @var ArrayCollection
	 */
	protected $context;
	
	/**
	 * @Column(type="datetime")
	 * @var \DateTime
	 */
	protected $created;
	
	/**
	 * @Column(type="integer")
	 * @var integer
	 */
	protected $severity;

	/**
	 * Construtor
	 * 
	 * @param integer $severity
	 * @param string $description
	 * @param array $context
	 */
	public function __construct( $severity, $description, array $context = array() ) {
		parent::__construct();
		$this->severity = $severity;
		$this->description = $description;
		$this->context = new ArrayCollection();
		foreach ( $context as $key => $value ) {
			$this->context[] = new LogContext($this, $key, $value);
		}
		$this->created = new \DateTime();
	}
	
	/**
	 * Obtem $description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Obtem $context
	 *
	 * @return array
	 */
	public function getContext() {
		$context = array();
		foreach($this->context as $name => $value); {
			$context[$name] = $value;
		}
		return $context;
	}

	/**
	 * Obtem $created
	 *
	 * @return \DateTime
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * Obtem $severity
	 *
	 * @return integer
	 */
	public function getSeverity() {
		return $this->severity;
	}

	
}
?>