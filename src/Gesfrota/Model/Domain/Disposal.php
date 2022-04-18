<?php
namespace Gesfrota\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Gesfrota\Model\Entity;

/**
 * Alienação
 * @Entity
 * @Table(name="disposals")
 * @EntityListeners({"Gesfrota\Model\Listener\DisposalListener", "Gesfrota\Model\Listener\LoggerListener"})
 */
class Disposal extends Entity {
	
	/**
	 * Rascunhada
	 * @var integer
	 */
	const DRAFTED = 0;
	
	/**
	 * Requisitada
	 * @var integer
	 */
	const REQUESTED = 1;
	
	/**
	 * Confirmada
	 * @var integer
	 */
	const CONFIRMED = 2;
	
	/**
	 * Recusada
	 * @var integer
	 */
	const DECLINED = 4;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $description;
	
	/**
	 * @Column(type="integer")
	 * @var integer
	 */
	protected $status;
	
	/**
	 * @Column(type="string")
	 * @var string
	 */
	protected $justify;
	
	/**
	 * @OneToMany(targetEntity="DisposalItem", mappedBy="disposal", indexBy="id", cascade={"all"})
	 * @var ArrayCollection
	 */
	protected $assets;
	
	/**
	 * @ManyToOne(targetEntity="Agency")
	 * @JoinColumn(name="requester_unit_id", referencedColumnName="id")
	 * @var Agency
	 */
	protected $requesterUnit;
	
	/**
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="requested_by", referencedColumnName="id")
	 * @var User
	 */
	protected $requestedBy;
	
	/**
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="confirmed_by", referencedColumnName="id")
	 * @var User
	 */
	protected $confirmedBy;
	
	/**
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="declined_by", referencedColumnName="id")
	 * @var User
	 */
	protected $declinedBy;
	
	/**
	 * @Column(name="requested_at", type="datetime")
	 * @var \DateTime
	 */
	protected $requestedAt;
	
	/**
	 * @Column(name="confirmed_at", type="datetime")
	 * @var \DateTime
	 */
	protected $confirmedAt;
	
	/**
	 * @Column(name="declined_at", type="datetime")
	 * @var \DateTime
	 */
	protected $declinedAt;
	
	/**
	 * @param Agency $agency
	 */
	public function __construct( Agency $agency ) {
		$this->status = self::DRAFTED;
		$this->requesterUnit = $agency;
		$this->requestedAt = new \DateTime();
		$this->assets = new ArrayCollection();
	}
	
	/**
	 * @return string
	 */
	public function getDescription() {
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getJustify() {
		return $this->justify;
	}

	/**
	 * @return integer
	 */
	public function getStatus() {
		return $this->status;
	}

	/**
	 * @param integer $status
	 * @throws \DomainException
	 */
	public function setStatus($status) {
		if (! self::isStatusAllowed($status) ) {
			throw new \DomainException('Disposal Status ' . $status . ' is not allowed.');
		}
		$this->status = $status;
	}

	/**
     * @param DisposalItem $item
     * @return bool
     */
	public function addAsset(DisposalItem $item) {
        $item->setDisposal($this);
        return $this->assets->add($item);
    }
    
    /**
     * @param integer|DisposalItem $card
     * @return false|DisposalItem
     */
    public function removeAsset($item) {
    	if ($item instanceof DisposalItem) {
        	return $this->assets->removeElement($item);
        } else {
        	return $this->assets->remove($item);
        }
    }
    
    /**
     * @return array
     */
    public function getAllAssets() {
    	return $this->assets->toArray();
    }
    
    /**
     * @return integer
     */
    public function getTotalAssets() {
    	return $this->assets->count();
    }
    
    /**
     * @return integer
     */
    public function getTotalAssetsValued() {
    	$criteria = Criteria::create()->where(Criteria::expr()->gt('value', 0));
    	return $this->assets->matching($criteria)->count();
    }

	/**
	 * @return Agency
	 */
	public function getRequesterUnit() {
		return $this->requesterUnit;
	}
	
	/**
	 * @return User
	 */
	public function getRequestedBy() {
		return $this->requestedBy;
	}

	/**
	 * @return User
	 */
	public function getConfirmedBy() {
		return $this->confirmedBy;
	}

	/**
	 * @return User
	 */
	public function getDeclinedBy() {
		return $this->declinedBy;
	}

	/**
	 * @return \DateTime
	 */
	public function getRequestedAt() {
		return $this->requestedAt;
	}
	
	/**
	 * @return \DateTime
	 */
	public function getConfirmedAt() {
		return $this->confirmedAt;
	}

	/**
	 * @return \DateTime
	 */
	public function getDeclinedAt() {
		return $this->declinedAt;
	}
	
	/**
	 * @param User $user
	 * @throws \DomainException
	 */
	public function toRequest(User $user) {
		if ($this->status != self::DRAFTED) {
			throw new \DomainException('Unable to request disposition of assets for disposal.');
		}
		$this->status = self::REQUESTED;
		foreach ($this->assets as $item) {
			$item->getAsset()->setActive(false);
		}
		$this->requestedBy = $user;
		$this->requestedAt = new \DateTime();
	}
	
	/**
	 * @param User $user
	 * @throws \DomainException
	 */
	public function toConfirm(User $user) {
		if ($this->status != self::REQUESTED) {
			throw new \DomainException('Unable to confirm disposition of assets for disposal.');
		}
		$this->status = self::CONFIRMED;
		$this->confirmedBy = $user;
		$this->confirmedAt = new \DateTime();
	}
	
	/**
	 * @param User $user
	 * @param string $justify
	 * @throws \DomainException
	 */
	public function toDecline(User $user, $justify) {
		if ($this->status < self::REQUESTED) {
			throw new \DomainException('Unable to decline disposition of assets for disposal.');
		}
		$this->status = self::DECLINED;
		$this->justify = $justify;
		$this->declinedBy = $user;
		$this->declinedAt = new \DateTime();
	}
	
	/**
	 * @throws \DomainException
	 */
	public function toDevolve() {
		if ($this->status == self::DRAFTED) {
			throw new \DomainException('Unable to devolve disposition of assets for disposal.');
		}
		$this->status = self::DRAFTED;
		$this->justify = null;
		$this->confirmedBy = null;
		$this->confirmedAt = null;
		$this->declinedBy = null;
		$this->declinedAt = null;
	}

	/**
	 * Obtem a lista de status permitidos
	 *
	 * @return string[]
	 */
	public static function getStatusAllowed() {
		return [self::DRAFTED => 'Rascunho',
				self::REQUESTED => 'Requisitada',
				self::CONFIRMED => 'Confirmada',
				self::DECLINED => 'Recusada'
		];
	}
	
	/**
     * Verifica se o status é permitido
     * 
     * @param integer $status
     * @return bool
     */
    public static function isStatusAllowed( int $status ) {
    	return array_key_exists($status, self::getStatusAllowed());
    }
	
}
?>