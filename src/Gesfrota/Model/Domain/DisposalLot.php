<?php
namespace Gesfrota\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Lote de Alienações
 * @Entity
 */
class DisposalLot extends Disposal {
	
	/**
	 * @OneToMany(targetEntity="Disposal", mappedBy="parent", indexBy="id", cascade={"all"})
	 * @var ArrayCollection
	 */
	protected $disposals;
	
	public function __construct() {
	    $this->status = self::DRAFTED;
	    $this->openedAt = new \DateTime();
	    $this->assets = new ArrayCollection();
	    $this->disposals = new ArrayCollection();
	}
	
	/**
	 * @return Disposal[]
	 */
	public function getDisposals() {
	    return $this->disposals->toArray();
	}
	
	/**
	 * @param DisposalItem $item
	 * @return bool
	 */
	public function addAsset(DisposalItem $item) {
	    throw new \BadMethodCallException('not call method');
	}
	
	/**
	 * @param integer|DisposalItem $card
	 * @return false|DisposalItem
	 */
	public function removeAsset($item) {
	    throw new \BadMethodCallException('not call method');
	}
	
	/**
	 * 
	 * @param Disposal $disposal
	 */
	public function addDisposal(Disposal $disposal) {
	    $disposal->setParent($this);
	    $this->disposals->set($disposal->getId(), $disposal);
	}
	
	/**
	 * @param integer|Disposal $disposal
	 * @return false|Disposal
	 */
	public function removeDisposal($disposal) {
	    if ($disposal instanceof Disposal) {
	        return $this->disposals->removeElement($disposal);
	    } else {
	        return $this->disposals->remove($disposal);
        }
	}
	
	/**
	 * @return array
	 */
	public function getAllAssets() {
	    $assets = [];
	    foreach ($this->disposals as $disposal) {
	        $assets+= $disposal->getAllAssets();
	    }
	    return $assets;
	}
	
	/**
	 * @return integer
	 */
	public function getAmountAssets(){
	    $total = 0;
	    foreach ($this->disposals as $disposal) {
	        $total+= $disposal->getAmountAssets();
	    }
	    return $total;
	}
	
	/**
	 * @return integer
	 */
	public function getAmountAssetsAppraise() {
	    $total = 0;
	    foreach ($this->disposals as $disposal) {
	        $total+= $disposal->getAmountAssetsAppraise();
	    }
	    return $total;
	}
	
	/**
	 * @param User $user
	 * @throws \DomainException
	 */
	public function toForward(User $user) {
	    $this->status = self::FORWARDED;
	    $this->forwardedBy = $user;
	    $this->forwardedAt = new \DateTime();
	    foreach ($this->disposals as $disposal) {
	        $disposal->toForward($user);
	    }
	}
}
?>