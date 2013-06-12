<?php
namespace Sigmat\Common\Logging;

/**
 * Contexto do log
 * 
 * @Entity 
 * @Table(name="logs_context")
 */
class LogContext {

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
	protected $key;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $value;
	/**
	 * @ManyToOne(targetEntity="Log", inversedBy="context")
	 * @JoinColumn(name="log_id", referencedColumnName="id")
	 * @var Log
	 */
	protected $log;

	/**
	 * Construtor
	 * 
	 * @param Log $log
	 * @param string $key
	 * @param mixed $value
	 */
	public function __construct( Log $log, $key, $value ) {
		$this->log = $log;
		$this->key = $key;
		$this->value = $value;
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
	 * Obtem a chave 
	 * 
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Obtem o valor 
	 * 
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Obtem o log
	 * 
	 * @return Log
	 */
	public function getLog() {
		return $this->log;
	}
}
?>