<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Driver;
use Gesfrota\Model\Domain\FleetManager;
use Gesfrota\Model\Domain\Manager;
use Gesfrota\Model\Domain\Requester;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Validate\Pattern\CPF;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Dropdown\Dropdown;
use PHPBootstrap\Widget\Dropdown\DropdownLink;
use PHPBootstrap\Widget\Dropdown\TgDropdown;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\CheckBoxList;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Badge;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;
use Gesfrota\Model\Domain\Agency;

class UserList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $newManager
	 * @param Action $newFleetManager
	 * @param Action $newDriver
	 * @param Action $newRequester
	 * @param Action $edit
	 * @param Action $active
	 * @param Action $password
	 * @param \Closure $profile
	 * @param array $agencies
	 */
	public function __construct( Action $filter, Action $newManager, Action $newFleetManager, Action $newDriver, Action $newRequester, Action $edit, Action $active, Action $password, \Closure $profile, array $agencies ) {
		$this->buildPanel('Segurança', 'Gerenciar Usuários');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('nif');
		$input->setSpan(2);
		$input->setMask('999.999.999-99');
		$input->setPattern(new CPF('Por favor, informe um CPF válido'));
		$form->buildField('CPF', $input);
		
		$input = new TextBox('name');
		$input->setSpan(5);
		$form->buildField('Nome', $input);
		
		$input = new ComboBox('lotation');
		$input->setSpan(5);
		$input->addOption(null, '');
		foreach($agencies as $agency) {
			$input->addOption($agency->getId(), $agency->getAcronym() . ' - ' . $agency->getName() . ' (' . $agency->getCode() . ')');
		}
		$form->buildField('Lotação', $input);
		
		$input = new CheckBoxList('type', true);
		$input->setOptions(['M' => Manager::USER_TYPE, 'F' => FleetManager::USER_TYPE, 'D' => Driver::USER_TYPE, 'R' => Requester::USER_TYPE]);
		$form->buildField(null, $input);
		
		$input = new CheckBox('only-active', 'Apenas ativos');
		$form->buildField(null, $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$modalFilter->setWidth(700);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$drop = new Dropdown();
		$drop->addItem(new DropdownLink('Administrador', new TgLink($newManager)));
		$drop->addItem(new DropdownLink('Gestor de Frota', new TgLink($newFleetManager)));
		$drop->addItem(new DropdownLink('Motorista', new TgLink($newDriver)));
		$drop->addItem(new DropdownLink('Requisitante', new TgLink($newRequester)));
		
		$this->buildToolbar(array(new Button('Novo', null, Button::Primary), new Button('', new TgDropdown($drop), Button::Primary)),
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('user-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('name', 'Usuário', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('class', null, null, 120, null, function($value) {
			$label = new Badge(constant($value. '::USER_TYPE'));
			switch ($value) {
				case Manager::getClass():
					$label->setStyle(Badge::Inverse);
					break;
					
				case FleetManager::getClass():
					$label->setStyle(Badge::Important);
					break;
					
				case Driver::getClass():
					$label->setStyle(Badge::Warning);
					break;
					
				case Requester::getClass():
					$label->setStyle(Badge::Info);
					break;
			}
			return $label;
		});
		$table->buildColumnText('nif', 'CPF', null, 120);
		$table->buildColumnText('lotation', 'Lotação', null, 100, null, function ($value) {
			return $value->getAgency()->getAcronym();
		});
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, User $user ) {
			$button->setIcon(new Icon($user->getActive() ? 'icon-remove' : 'icon-ok'));
		});
		$table->buildColumnAction('profile', new Icon('icon-user'), null, null, $profile);
		$table->buildColumnAction('reset', new Icon('icon-asterisk'), $password);
		
	}
	
}
?>