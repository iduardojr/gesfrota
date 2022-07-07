<?php
namespace Gesfrota\Model\Domain;


/**
 * @Entity
 * @Table(name="notice_read_by_users")
 */
class NoticeRead {
    
    /**
     * @Id
     * @OneToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     * @var User
     */
    protected $user;
    
    /**
     * @Id
     * @ManyToOne(targetEntity="Notice", inversedBy="readBy")
     * @JoinColumn(name="notice_id", referencedColumnName="id")
     * @var Notice
     */
    protected $notice;
    
    /**
     * @Column(name="read_at", type="datetime")
     * @var \DateTime
     */
    protected $readAt;
    
    /**
     * @param Notice $notice
     * @param User $user
     */
    public function __construct(Notice $notice, User $user) {
        $this->notice = $notice;
        $this->user = $user;
        $this->readAt = new \DateTime();
    }
    
    /**
     * @return User
     */
    public function getUser() {
        return $this->user;
    }
    
    /**
     * @return Notice
     */
    public function getNotice() {
        return $this->notice;
    }
    
    /**
     * @return \DateTime
     */
    public function getReadAt() {
        return $this->readAt;
    }
    
    /**
     * @return integer
     */
    public function getCode() {
        return $this->user->getCode();
    }
    
    /**
     * @return string
     */
    public function getName() {
        return $this->user->getName();
    }
    
    /**
     * @return string
     */
    public function getNif() {
        return $this->user->getNif();
    }
    
    /**
     * @return string
     */
    public function getUserType() {
        return $this->user->getUserType();
    }
    
    /**
     * @return string
     */
    public function getLotation() {
        return $this->user->getLotation()->getAgency()->getAcronym();
    }
}