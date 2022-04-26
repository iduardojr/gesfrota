<?php
namespace Gesfrota\Services;

use PHPBootstrap\Mvc\Auth\Adapter\Adapter;
use Doctrine\ORM\EntityManager;
use Gesfrota\Model\Domain\User;
use Gesfrota\Util\Crypt;
use Gesfrota\Model\Domain\Manager;

class UserAdapter extends Adapter {
	
	/**
	 * @var EntityManager
	 */
	protected $em;
	
	/**
	 * @var User
	 */
	protected $user;
	
	public function __construct() {
	}
	
	public function setEntityManager(EntityManager $em) {
		$this->em = $em;
	}
	
	public function getByIdentity( $identity ) {
		$builder = $this->em->getRepository(User::getClass())->createQueryBuilder('u');
		$builder->andWhere('u.nif = :identity');
		$builder->andWhere('u.active = true');
		$builder->setParameter('identity', $identity);
		$this->user = $builder->getQuery()->getSingleResult();
		return $this->user;
	}
	
	public function getCredential() {
		return $this->user->getPassword();
	}
	
	public function algoSecure( $credential ) {
		return Crypt::decode($credential);
	}
	
	public function getData() {
		if ($this->user instanceof Manager) {
			return ['user-id' => $this->user->getId(),
					'lotation-id' => 0
			];
		}
		return ['user-id' => $this->user->getId(),
				'lotation-id' => $this->user->getLotation()->getAgency()->getId()
		];
	}
}