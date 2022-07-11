<?php
namespace Gesfrota\Model\Domain;

use Doctrine\Common\Collections\ArrayCollection;
use Gesfrota\Model\AbstractActivable;

/**
 * Notificação
 * 
 * @Entity
 * @Table(name="notices")
 * @EntityListeners({"Gesfrota\Model\Listener\NoticeListener"})
 */
class Notice extends AbstractActivable {
    
    /**
     * Sobre
     * 
     * @var integer
     */
    const ABOUT = 1;
    
    /**
     * @Column(type="string")
     * @var string
     */
    protected $title;
    
    /**
     * @Column(type="string")
     * @var string
     */
    protected $body;
    
    /**
     * @OneToMany(targetEntity="NoticeRead", mappedBy="notice", indexBy="user_id", cascade={"all"})
     * @var ArrayCollection
     */
    protected $readBy;
    
    /**
     * @Column(name="created_at", type="datetime")
     * @var \DateTime
     */
    protected $createdAt;
    
    /**
     * @Column(name="updated_at", type="datetime")
     * @var \DateTime
     */
    protected $updatedAt;
    
    
    public function __construct() {
        parent::__construct();
        $this->readBy = new ArrayCollection();
        $this->createdAt = $this->updatedAt = new \DateTime();
    }
    
    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getBody() {
        return $this->body;
    }
    
    /**
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    /**
     * @return \DateTime
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
    }
    
    /**
     * @return mixed[]
     */
    public function getReadByUsers() {
        return $this->readBy->toArray();
    }
    
    /**
     * @return integer
     */
    public function getReadAmount() {
        return $this->readBy->count();
    }

    /**
     * @param string $title
     */
    public function setTitle( $title ) {
        $this->title = $title;
        $this->updatedAt = new \DateTime();
    }

    /**
     * @param string $body
     */
    public function setBody( $body ) {
        $this->body = $body;
        $this->updatedAt = new \DateTime();
    }
    
    /**
     * @param User $user
     * @return boolean
     */
    public function isReadBy(User $user) {
        return $this->readBy->containsKey($user->getId());
    }
    
    
    /**
     * @param User $user
     */
    public function readBy(User $user) {
        $this->readBy->set($user->getId(), new NoticeRead($this, $user));
    }
    
    
    /**
     * @return boolean
     */
    public function canDelete() {
        $rf = new \ReflectionClass($this);
        return array_search($this->id, $rf->getConstants(), true) === false;
    }
}
?>