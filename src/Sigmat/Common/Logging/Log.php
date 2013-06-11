<?php
namespace Sigmat\Common\Logging;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Evento de Log
 * 
 * @Entity 
 * @Table(name="logs")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="event", type="string")
 * @DiscriminatorMap({"Access" = "LogAccess", "Application" = "LogApplication"})
 */
abstract class Log {
	
	// Severidade
	const Critical = 400;
	const Error = 300;
	const Warning = 200;
	const Notice = 100;
	
	/**
	 * @Id 
	 * @Column(type="integer") 
	 * @GeneratedValue 
	 * @var integer
	 */
	protected $id;
	
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
		$this->severity = $severity;
		$this->description = $description;
		$this->context = new ArrayCollection();
		foreach ( $context as $key => $value ) {
			$this->context[] = new LogContext($this, $key, $value);
		}
		$this->created = new \DateTime();
	}

	/**
	 * Obtem o identificador
	 * 
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Obtem a descricao
	 * 
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * Obtem o contexto
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
	 * Obtem a data de criaчуo
	 * 
	 * @return string
	 */
	public function getCreated() {
		return $this->created;
	}

	/**
	 * Obtem a severidade
	 * 
	 * @return integer
	 */
	public function getSeverity() {
		return $this->severity;
	}
	
}
?>