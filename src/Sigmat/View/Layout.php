<?php
namespace Sigmat\View;

use PHPBootstrap\Mvc\View\View;
use PHPBootstrap\Widget\Nav\Navbar;
use PHPBootstrap\Widget\Nav\NavBrand;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Nav\Nav;
use PHPBootstrap\Widget\Nav\NavLink;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Dropdown\TgDropdown;
use PHPBootstrap\Widget\Dropdown\Dropdown;
use PHPBootstrap\Widget\Dropdown\DropdownLink;
use PHPBootstrap\Widget\Dropdown\DropdownDivider;
use PHPBootstrap\Widget\Nav\NavItem;
use PHPBootstrap\Widget\Nav\NavDivider;
use PHPBootstrap\Widget\Nav\NavHeader;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Modal\TgModalOpen;

/**
 * Layout
 */
class Layout extends View {
	
	/**
	 * Construtor
	 */
	public function __construct( $content = null, $layout = 'layout/layout.phtml' ) {
		parent::__construct();
		$this->buildNavbar();
		$this->setContent($content);
		$this->setLayout($layout);
	}
	
	/**
	 * Constroi a barra de navegação
	 * 
	 * @return Navbar
	 */
	protected function buildNavbar() {
		$about = $this->buildAbout();
		
		$navbar = new Navbar('navbar', new NavBrand('Sigmat <sup>v1.0</sup>', new TgLink(new Action('Sigmat\\Controller\\IndexController'))), true);
		$navbar->setDisplay(Navbar::FixedTop);
		$nav = new Nav();
		$nav->addItem(new NavDivider());
		$nav->addItem(new NavLink('Movimentações'));
		$nav->addItem(new NavDivider());
		$nav->addItem(new NavLink('Requisições'));
		$nav->addItem(new NavDivider());
		$nav->addItem(new NavLink('Ajustes'));
		$nav->addItem(new NavDivider());
		$nav->addItem(new NavLink('Relatórios'));
		$nav->addItem(new NavDivider());
		
		$drop = new Dropdown();
		$drop->addItem(new DropdownLink('Gerenciar Orgãos', new TgLink(new Action('Sigmat\\Controller\\AgencyController'))));
		$drop->addItem(new DropdownLink('Gerenciar Almoxarifados', new TgLink(new Action('Sigmat\\Controller\\StockroomController'))));
		$drop->addItem(new DropdownLink('Gerenciar Unidades Administrativas', new TgLink(new Action('Sigmat\\Controller\\AdministrativeUnitController'))));
		$nav->addItem(new NavLink('Administração', new TgDropdown($drop, false, false)));
		$navbar->addItem($nav);
		
		$nav = new Nav();
		
		$drop = new Dropdown();
		$drop->addItem(new DropdownLink('Ajuda'));
		$drop->addItem(new DropdownDivider());
		$drop->addItem(new DropdownLink('Sobre o Sigmat...', new TgModalOpen($about)));
		$nav->addItem(new NavLink(new Icon('icon-question-sign', true), new TgDropdown($drop, false, false)));
		
		$drop = new Dropdown();
		$drop->addItem(new DropdownLink('Editar minha conta'));
		$drop->addItem(new DropdownLink('Alterar orgão de acesso'));
		$drop->addItem(new DropdownLink('Configurar'));
		$drop->addItem(new DropdownDivider());
		$drop->addItem(new DropdownLink('Logout'));
		$nav->addItem(new NavDivider());
		$nav->addItem(new NavLink(new Icon('icon-user', true), new TgDropdown($drop, false, false)));
		
		$nav->addItem(new NavHeader('SEGPLAN'));
		
		$navbar->addItem($nav, NavItem::Right);
		
		$this->navbar = $navbar;
		return $navbar;
	}
	
	/**
	 * Constroi o modal sobre
	 * 
	 * @return Modal
	 */
	protected function buildAbout() {
		$about = new Modal('about', new Title('Sobre', 3));
		$text = "Before downloading, be sure to have a code editor (we recommend Sublime Text 2)
				 and some working knowledge of HTML and CSS. We won't walk through the source files here,
				 but they are available for download. We'll focus on getting started with the compiled
				 Bootstrap files.";
		$about->setBody(new Paragraph($text));
		$about->addButton(new Button('Ok', new TgModalClose(), Button::Primary));
		$this->about = $about;
		return $about;
	}
	
	public function __toString() {
		try {
			return parent::__toString();
		} catch ( \Exception $e ) {
			var_dump($e->getMessage());
			var_dump($e->getTrace());
		}
	}
}
?>