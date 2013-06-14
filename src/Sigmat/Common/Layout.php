<?php
namespace Sigmat\Common;

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

/**
 * Layout
 */
class Layout extends View {
	
	/**
	 * Construtor
	 */
	public function __construct( $content = null ) {
		parent::__construct();
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
		$nav->addItem(new NavLink('Administração'));
		$navbar->addItem($nav);
		
		$nav = new Nav();
		$drop = new Dropdown();
		$drop->addItem(new DropdownLink('Edit profile'));
		$drop->addItem(new DropdownLink('Configure'));
		$drop->addItem(new DropdownDivider());
		$drop->addItem(new DropdownLink('Logout'));
		$nav->addItem(new NavLink(new Icon('icon-user', true), new TgDropdown($drop, false, false)));
		$nav->addItem(new NavDivider());
		$nav->addItem(new NavLink(new Icon('icon-question-sign', true), new TgDropdown($drop, false, false)));
		$navbar->addItem($nav, NavItem::Right);
		$this->__NAVBAR__ = $navbar;
		
		if ( $content !== null ) {
			$this->setContent($content);
		}
		
		$this->setLayout('layout/layout.phtml');
	}
}
?>