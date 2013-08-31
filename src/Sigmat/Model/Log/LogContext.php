<?php
namespace Sigmat\Model\Log;

use Sigmat\Model\Entity;

/**
 * Contexto do log
 * 
 * @Entity 
 * @Table(name="logs_context")
 */
class LogContext extends Entity {

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
		parent::__construct();
		$this->log = $log;
		$this->key = $key;
		$this->value = $value;
	}
	
	/**
	 * Obtem $key
	 *
	 * @return string
	 */
	public function getKey() {
		return $this->key;
	}

	/**
	 * Obtem $value
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Obtem $log
	 *
	 * @return Log
	 */
	public function getLog() {
		return $this->log;
	}

	
}
?>