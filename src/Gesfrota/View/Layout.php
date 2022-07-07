<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Notice;
use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\TrafficController;
use Gesfrota\Model\Domain\User;
use Gesfrota\Services\AclResource;
use PHPBootstrap\Mvc\View\View;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Dropdown\Dropdown;
use PHPBootstrap\Widget\Dropdown\DropdownDivider;
use PHPBootstrap\Widget\Dropdown\DropdownHeader;
use PHPBootstrap\Widget\Dropdown\DropdownLink;
use PHPBootstrap\Widget\Dropdown\TgDropdown;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Nav\Nav;
use PHPBootstrap\Widget\Nav\NavBrand;
use PHPBootstrap\Widget\Nav\NavDivider;
use PHPBootstrap\Widget\Nav\NavHeader;
use PHPBootstrap\Widget\Nav\NavItem;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Nav\Navbar;


/**
 * Layout
 */
class Layout extends View {
	
	/**
	 * Construtor
	 */
	public function __construct( $content = null, $layout = 'layout/layout.phtml' ) {
		parent::__construct();
		$this->setContent($content);
		$this->setLayout($layout);
	}
	
	/**
	 * Constroi o modal sobre
	 * 
	 * @return Modal
	 */
	protected function buildAbout(Notice $content) {
	    $about = new Modal('about', new Title($content->getTitle(), 3));
		$about->setWidth(650);
		$about->setBody(new Panel($content->getBody()));
		$this->about = $about;
		return $about;
	}
	
	/**
	 * Constroi a barra de navegação
	 * 
	 * @param AclResource $acl
	 * @param integer $noticesAmount
	 * @param User $role
	 * @param string $agency
	 * 
	 * @return Navbar
	 */
	public function BuiderNavbar(Notice $about, $noticesAmount, AclResource $acl, User $role, $agency) {
		
		$brand = new Action('Gesfrota\\Controller\\IndexController');
		$navbar = new Navbar('navbar', new NavBrand('Gesfrota', new TgLink($brand)), true);
		$navbar->setDisplay(Navbar::FixedTop);
		
		$nav = new Nav();
		
// 		$resource1 = 'Gesfrota\\Controller\\IndexController';
// 		if ($acl->isAllowed($role, $resource1)) {
// 			$nav->addItem(new NavLink('Dashboard', new TgLink(new Action($resource1))));
// 		}
		
		$resource1 = 'Gesfrota\\Controller\\FleetController';
		$resource2 = 'Gesfrota\\Controller\\DisposalController';
		if ($acl->isAllowed($role, $resource1) || $acl->isAllowed($role, $resource2)) {
			$drop = new Dropdown();
			$nav->addItem(new NavLink('Frota', new TgDropdown($drop, false, false)));
			
			$drop->addItem(new DropdownHeader('Minha Frota'));
			if ($acl->isAllowed($role, $resource1)) {
				$drop->addItem(new DropdownLink('Gerenciar Veículos e Equipamentos', new TgLink(new Action($resource1))));
			}
			if ($acl->isAllowed($role, $resource2)) {
				$drop->addItem(new DropdownLink('Gerenciar Disposições para Alienação', new TgLink(new Action($resource2))));
			}
		}
		
		$resource1 = 'Gesfrota\\Controller\\DriverController';
		if ($acl->isAllowed($role, $resource1)) {
			$nav->addItem(new NavLink('Motoristas', new TgLink(new Action($resource1))));
		}
		
		$resource1 = 'Gesfrota\\Controller\\RequesterController';
		if ($acl->isAllowed($role, $resource1)) {
			$nav->addItem(new NavLink('Requisitantes', new TgLink(new Action($resource1))));
		}
		
		$resource1 = 'Gesfrota\\Controller\\RequestController';
		if ($acl->isAllowed($role, $resource1)) {
			if ($role instanceof Requester || $role instanceof Driver || $role instanceof TrafficController) {
				$brand->setClassName($resource1);
			} else {
				$nav->addItem(new NavLink('Viagens', new TgLink(new Action($resource1))));
			}
		}
		
// 		if ($acl->isAllowed($role, AclResource::Reports)) {
// 			$nav->addItem(new NavLink('Relatórios'));
// 		}
		
		$drop = new Dropdown();
		$isAdministrator = false;
		$isDivider = false;
		
		$resource1 = 'Gesfrota\\Controller\\AgencyController';
		$resource2 = 'Gesfrota\\Controller\\AdministrativeUnitController';
		$resource3 = 'Gesfrota\\Controller\\ResultCenterController';
		if ( $acl->isAllowed($role, $resource1) || $acl->isAllowed($role, $resource2) || $acl->isAllowed($role, $resource3)) {
			$drop->addItem(new DropdownHeader('Estrutura Organizacional'));
			if ($acl->isAllowed($role, $resource1)) {
				$drop->addItem(new DropdownLink('Gerenciar Órgãos', new TgLink(new Action($resource1))));
			}
			if ($acl->isAllowed($role, $resource2)) {
				$drop->addItem(new DropdownLink('Gerenciar Unidades Administrativas', new TgLink(new Action($resource2))));
			}
			if ($acl->isAllowed($role, $resource3)) {
				$drop->addItem(new DropdownLink('Gerenciar Centro de Resultados', new TgLink(new Action($resource3))));
			}
			$isAdministrator = true;
			$isDivider = true;
		}
		
		$resource1 = 'Gesfrota\\Controller\\OwnerController';
		$resource2 = 'Gesfrota\\Controller\\ServiceProviderController';
		if ($acl->isAllowed($role, $resource1) || $acl->isAllowed($role, $resource2) ) {
			if ($isDivider) {
				$drop->addItem(new DropdownDivider());
			}
			$drop->addItem(new DropdownHeader('Entidades Externas'));
			if ($acl->isAllowed($role, $resource1)) {
				$drop->addItem(new DropdownLink('Gerenciar Proprietários', new TgLink(new Action($resource1))));
			}
			if ($acl->isAllowed($role, $resource2)) {
				$drop->addItem(new DropdownLink('Gerenciar Prestadores de Serviço', new TgLink(new Action($resource2))));
			}
			$isDivider = true;
			$isAdministrator = true;
		}
		
		
		$resource1 = 'Gesfrota\\Controller\\VehicleFamilyController';
		$resource2 = 'Gesfrota\\Controller\\VehicleMakerController';
		$resource3 = 'Gesfrota\\Controller\\VehicleModelController';
		if ($acl->isAllowed($role, $resource1) || $acl->isAllowed($role, $resource2) || $acl->isAllowed($role, $resource3)) {
			if ($isDivider) {
				$drop->addItem(new DropdownDivider());
			}
			$drop->addItem(new DropdownHeader('Especificações de Veículos'));
			if ($acl->isAllowed($role, $resource1)) {
				$drop->addItem(new DropdownLink('Gerenciar Família', new TgLink(new Action($resource1))));
			}
			if ($acl->isAllowed($role, $resource2)) {
				$drop->addItem(new DropdownLink('Gerenciar Fabricante', new TgLink(new Action($resource2))));
			}
			if ($acl->isAllowed($role, $resource3)) {
				$drop->addItem(new DropdownLink('Gerenciar Modelo', new TgLink(new Action($resource3))));
			}
			$isDivider = true;
			$isAdministrator = true;
		}
		
		$resource1 = 'Gesfrota\\Controller\\NoticeController';
		$resource2 = 'Gesfrota\\Controller\\ImportController';
		if ($acl->isAllowed($role, $resource1) || $acl->isAllowed($role, $resource2) ) {
		    if ($isDivider) {
		        $drop->addItem(new DropdownDivider());
		    }
		    $drop->addItem(new DropdownHeader('Sistema'));
		    if ($acl->isAllowed($role, $resource1)) {
		        $drop->addItem(new DropdownLink('Gerenciar Notificação', new TgLink(new Action($resource1))));
		    }
		    if ($acl->isAllowed($role, $resource2)) {
		        $drop->addItem(new DropdownLink('Gerenciar Importações', new TgLink(new Action($resource2))));
		    }
		    $isAdministrator = true;
		}
		
		$resource1 = 'Gesfrota\\Controller\\UserController';
		$resource2 = 'Gesfrota\\Controller\\AuditController';
		if ($acl->isAllowed($role, $resource1) || $acl->isAllowed($role, $resource2) ) {
			if ($isDivider) {
				$drop->addItem(new DropdownDivider());
			}
			$drop->addItem(new DropdownHeader('Segurança'));
			if ($acl->isAllowed($role, $resource1)) {
				$drop->addItem(new DropdownLink('Gerenciar Usuários', new TgLink(new Action($resource1))));
			}
			if ($acl->isAllowed($role, $resource2)) {
				$drop->addItem(new DropdownLink('Auditoria', new TgLink(new Action($resource2))));
			}
			$isAdministrator = true;
		}
		
		
		if ($isAdministrator) {
			$nav->addItem(new NavLink('Administração', new TgDropdown($drop, false, false)));
		}
		$navbar->addItem($nav);
		
		
		$nav = new Nav();
		
    	if ( $about->getActive() ) {
    		$nav->addItem(new NavLink(new Icon('icon-question-sign', true), new TgModalOpen($this->buildAbout($about))));
    	} 
    	
		$resource1 = 'Gesfrota\\Controller\\AccountController';
		if ($acl->isAllowed($role, $resource1) ) {
		    $badge = $noticesAmount > 0 ? ' <span class="badge badge-warning">' . $noticesAmount . '</span>' : '';
		    $link = new NavLink(new Icon('icon-bell', true), new TgLink(new Action($resource1, 'notices')));
		    $link->setLabel($badge);
		    $nav->addItem($link);
		    $nav->addItem(new NavDivider());
		    
			$drop = new Dropdown();
			$drop->addItem(new DropdownHeader('Minha Conta'));
			$drop->addItem(new DropdownLink('Editar Perfil', new TgLink(new Action($resource1))));
			$drop->addItem(new DropdownLink('Alterar Senha', new TgLink(new Action($resource1, 'changePassword'))));
			if ($acl->isAllowed($role, $resource1, 'access')) {
				$drop->addItem(new DropdownLink('Alterar Acesso', new TgLink(new Action($resource1, 'access'))));
			}
			$drop->addItem(new DropdownDivider());
			$drop->addItem(new DropdownLink('Logout', new TgLink(new Action('Gesfrota\\Controller\\AuthController', 'logout'))));
			
			$link = new NavLink(new Icon('icon-user', true), new TgDropdown($drop, false, false));
			$link->setLabel($role->getFirstName());
			$nav->addItem($link);
		}
		
		$nav->addItem(new NavHeader($agency));
		
		$navbar->addItem($nav, NavItem::Right);
		
		$this->navbar = $navbar;
		
		return $navbar;
	}
	
}
?>