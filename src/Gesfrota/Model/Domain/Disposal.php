<?php
namespace Gesfrota\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Gesfrota\Model\NestedSet\Node;

/**
 * Alienação
 * @Entity
 * @Table(name="disposals")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"D" = "Gesfrota\Model\Domain\Disposal", "L" = "Gesfrota\Model\Domain\DisposalLot"})
 * @EntityListeners({"Gesfrota\Model\NestedSet\NodeListener", "Gesfrota\Model\Listener\DisposalListener", "Gesfrota\Model\Listener\LoggerListener"})
 */
class Disposal extends Node {
	
	/**
	 * Rascunhada
	 * @var integer
	 */
	const DRAFTED = 0;
	
	/**
	 * Avaliada
	 * @var integer
	 */
	const APPRAISED = 1;
	
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
	 * Encaminhada
	 * @var integer
	 */
	const FORWARDED = 8;
	
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
	 * @OneToOne(targetEntity="DisposalLot", fetch="EAGER")
	 * @JoinColumn(name="parent_id", referencedColumnName="id")
	 * @var DisposalLot
	 */
	protected $parent;
	
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
	 * @JoinColumn(name="agency_id", referencedColumnName="id")
	 * @var Agency
	 */
	protected $agency;
	
	/**
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="appraised_by", referencedColumnName="id")
	 * @var User
	 */
	protected $appraisedBy;
	
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
	 * @ManyToOne(targetEntity="User")
	 * @JoinColumn(name="forwarded_by", referencedColumnName="id")
	 * @var User
	 */
	protected $forwardedBy;
	
	/**
	 * @Column(name="opened_at", type="datetime")
	 * @var \DateTime
	 */
	protected $openedAt;
	
	/**
	 * @Column(name="appraised_at", type="datetime")
	 * @var \DateTime
	 */
	protected $appraisedAt;
	
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
	 * @Column(name="forwarded_at", type="datetime")
	 * @var \DateTime
	 */
	protected $forwardedAt;
	
	/**
	 * @param Agency $agency
	 * @param DisposalLot $disposalLot
	 */
	public function __construct( Agency $agency, DisposalLot $disposalLot ) {
		$this->status = self::DRAFTED;
		$this->agency = $agency;
		$this->openedAt = new \DateTime();
		$this->assets = new ArrayCollection();
		$this->setParent($disposalLot);
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
     * @param DisposalLot $parent
     */
    public function setParent(DisposalLot $parent) {
        $this->parent = $parent;
    }
    
    /**
     * @return DisposalLot
     */
    public function getParent() {
        return $this->parent;
    }
    
    /**
     * @param integer|DisposalItem $item
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
    public function getAmountAssets() {
    	return $this->assets->count();
    }
    
    /**
     * @return integer
     */
    public function getAmountAssetsAppraise() {
    	$criteria = Criteria::create()->where(Criteria::expr()->gt('value', 0));
    	return $this->assets->matching($criteria)->count();
    }
    
    /**
     * @return number
     */
    public function getTotalValue() {
        $total = 0;
        $assets = $this->getAllAssets();
        foreach ($assets as $asset) {
            $total+= $asset->getValue();
        }
        return $total;
    }
    
    /**
     * @return number
     */
    public function getTotalDebit() {
        $total = 0;
        $assets = $this->getAllAssets();
        foreach ($assets as $asset) {
            $asset instanceof DisposalItem;
            $total+= $asset->getDebit();
        }
        return $total;
    }

	/**
	 * @return Agency
	 */
	public function getAgency() {
		return $this->agency;
	}
	
	/**
	 * @return User
	 */
	public function getAppraisedBy() {
	    return $this->appraisedBy;
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
     * @return User
     */
    public function getForwardedBy() {
        return $this->forwardedBy;
    }
    
    /**
     * @return \DateTime
     */
    public function getOpenedAt() {
        return $this->openedAt;
    }

    /**
	 * @return \DateTime
	 */
    public function getAppraisedAt() {
        return $this->appraisedAt;
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
	 * @return \DateTime
	 */
	public function getForwardedAt() {
	    return $this->forwardedAt;
	}
	
	/**
	 * @param User $user
	 * @throws \DomainException
	 */
	public function toAppraise(User $user) {
		if ($this->status != self::DRAFTED) {
			throw new \DomainException('Unable to request disposition of assets for disposal.');
		}
		$this->status = self::APPRAISED;
		foreach ($this->assets as $item) {
			$item->getAsset()->setActive(false);
		}
		$this->appraisedBy = $user;
		$this->appraisedAt = new \DateTime();
	}
	
	/**
	 * @param User $user
	 * @throws \DomainException
	 */
	public function toConfirm(User $user) {
		if ($this->status != self::APPRAISED) {
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
		if ($this->status < self::APPRAISED) {
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
	    if ($this->status == self::DRAFTED || $this->status == self::FORWARDED) {
			throw new \DomainException('Unable to devolve disposition of assets for disposal.');
		}
		$this->status = self::DRAFTED;
		$this->justify = null;
		$this->appraisedAt = null;
		$this->appraisedBy = null;
		$this->confirmedBy = null;
		$this->confirmedAt = null;
		$this->declinedBy = null;
		$this->declinedAt = null;
	}
	
	/**
	 * @param User $user
	 * @throws \DomainException
	 */
	public function toForward(User $user) {
	    if ($this->status != self::CONFIRMED) {
	        throw new \DomainException('Unable to forward disposition of assets for disposal.');
	    }
	    $this->status = self::FORWARDED;
	    $this->forwardedBy = $user;
	    $this->forwardedAt = new \DateTime();
	}

	/**
	 * Obtem a lista de status permitidos
	 *
	 * @return string[]
	 */
	public static function getStatusAllowed() {
		return [self::DRAFTED => 'Rascunho',
				self::APPRAISED => 'Avaliada',
				self::CONFIRMED => 'Confirmada',
				self::DECLINED => 'Recusada',
		        self::FORWARDED => 'Encaminhada'
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