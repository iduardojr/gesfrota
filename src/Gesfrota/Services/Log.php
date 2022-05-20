<?php
namespace Gesfrota\Services;

use Gesfrota\Model\Entity;
use Gesfrota\Model\Domain\Agency;
use Gesfrota\Model\Domain\User;
use Gesfrota\Util\Format;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Log
 * @Entity
 * @Table(name="logs")
 * @HasLifecycleCallbacks
 */
class Log {
	
	/**
	 * @Id
	 * @Column(type="integer")
	 * @GeneratedValue
	 * @var integer
	 */
	protected $id;
	
	/**
	 * @Column(type="string", name="classname")
	 * @var string
	 */
	protected $className;
	
	/**
	 * @Column(type="integer")
	 * @var integer
	 */
	protected $oid;
	
	/**
	 * @Column(type="array", name="old_value")
	 * @var array
	 */
	protected $oldValue;
	
	/**
	 * @Column(type="array", name="new_value")
	 * @var array
	 */
	protected $newValue;
	
	/**
	 * @Column(type="datetime")
	 * @var \DateTime
	 */
	protected $created;
	
	/**
	 * @ManyToOne(targetEntity="Gesfrota\Model\Domain\User")
	 * @JoinColumn(name="user_id", referencedColumnName="id")
	 * @var User
	 */
	protected $user;
	
	/**
	 * @ManyToOne(targetEntity="Gesfrota\Model\Domain\Agency")
	 * @JoinColumn(name="agency_id", referencedColumnName="id")
	 * @var Agency
	 */
	protected $agency;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $referer;
	
	
	/**
	 * @param string $referer
	 * @param User $user
	 * @param Agency $agency
	 * @param Entity $newValue
	 * @param Entity $oldValue
	 */
	public function __construct($referer, User $user, Agency $agency, $newValue, $oldValue) {
		$this->created = new \DateTime('now');
		$this->referer = $referer;
		if ($newValue instanceof User && $user->getId() !== $newValue->getId()) {
			$this->user = $user;
		}
		$this->agency = $agency;
		$this->oldValue = $oldValue;
		$this->newValue = $newValue;
		$this->className = str_replace('DoctrineProxies\\__CG__\\', '', get_class(is_object($oldValue) ? $oldValue : $newValue));
		$this->oid = is_object($oldValue) ? $oldValue->getId() : $newValue->getId();
	}
	
	/**
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return string
	 */
	public function getCode() {
		return Format::code($this->id, 6);
	}
	
	/**
	 * @return string
	 */
	public function getClassName() {
		return $this->className;
	}
	
	/**
	 * @return integer
	 */
	public function getOID() {
		return $this->oid;
	}
	
	/**
	 * @return string
	 */
	public function getInstance() {
		return $this->className . ' #'.$this->oid;
	}

	/**
	 * @return array
	 */
	public function getOldValue() {
		return $this->oldValue;
	}

	/**
	 * @return array
	 */
	public function getNewValue() {
		return $this->newValue;
	}
	
	/**
	 * @return \DateTime
	 */
	public function getCreated() {
		return $this->created;
	}
	
	/**
	 * @return User
	 */
	public function getUser() {
		return $this->user;
	}

	/**
	 * @return Agency
	 */
	public function getAgency() {
		return $this->agency;
	}
	
	/**
	 * @return string
	 */
	public function getReferer() {
		return $this->referer;
	}
	
	/**
	 * @param Entity $object
	 */
	protected function transform(Entity $object) {
		$vars = $object->toArray();
		foreach($vars as $var => $val) {
			if ($val instanceof Entity) {
				$vars[$var] = str_replace('DoctrineProxies\\__CG__\\', '', get_class($val)) . '('.$val->getId().')';
			} elseif ($val instanceof Collection) {
				$collection = $val->toArray();
				foreach($collection as $key => $item) {
					if ($item instanceof Entity) {
						$collection[$key] = str_replace('DoctrineProxies\\__CG__\\', '', get_class($item)) . '('.$item->getId().')';
					}
				}
				$vars[$var] = $collection;
			}
		}
		unset($vars['__isInitialized__']);
		unset($vars['__cloner__']);
		unset($vars['__initializer__']);
		unset($vars['id']);
		return $vars;
	}
	
	/**
	 * @PrePersist
	 * @param LifecycleEventArgs $event
	 */
	public function prePersist(LifecycleEventArgs $event) {
		$this->oldValue = $this->oldValue instanceof Entity ? $this->transform($this->oldValue) : null;
		$this->newValue = $this->newValue instanceof Entity ? $this->transform($this->newValue) : null;
	}
	
	/**
	 * Obtem uma propriedade
	 *
	 * @param string $name
	 * @throws \RuntimeException
	 */
	public function __get( $name ) {
		$method = 'get' . ucfirst($name);
		if ( ! method_exists( $this, $method ) ) {
			throw new \BadMethodCallException('unsupported method "' . $method . '" in ' . get_class($this));
		}
		return call_user_func(array(&$this, $method));
	}

}
?>