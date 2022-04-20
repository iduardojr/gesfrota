<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Validate\Pattern\CPF;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\Decorator\Suggest;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Layout\Panel;
use PHPBootstrap\Widget\Form\Controls\Decorator\Seek;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Form\TgFormSubmit;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use Gesfrota\Model\Domain\AdministrativeUnit;

class RequesterList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $lotation
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $active
	 * @param Action $search
	 * @param Action $transfer
	 * @param Action $password
	 */
	public function __construct( Action $filter, Action $lotation, Action $new, Action $edit, Action $active, Action $search, Action $transfer, Action $password, array $showAgencies = null) {
		$this->buildPanel('Minha Frota', 'Gerenciar Requisitantes');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		if ($showAgencies) {
			$input = new ComboBox('agency');
			$input->setSpan(2);
			$input->setOptions($showAgencies);
			$form->buildField('Órgão', $input);
		}
		
		$input = new TextBox('nif');
		$input->setSpan(2);
		$input->setMask('999.999.999-99');
		$input->setPattern(new CPF('Por favor, informe um CPF válido'));
		$form->buildField('CPF', $input);
		
		$input = new TextBox('name');
		$input->setSpan(7);
		$form->buildField('Nome', $input);
		
		if (!$showAgencies) {
			$input = new TextBox('lotation');
			$input->setSuggestion(new Suggest($lotation, 3));
			$input->setSpan(7);
			$form->buildField('Lotação', [$input, new Hidden('lotation-id')]);
		}
		
		$input = new CheckBox('only-active', 'Apenas ativos');
		$form->buildField(null, $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$modalFilter->setWidth(900);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$form = new BuilderForm('transfer-requester-form');
		$form->append(new Panel(null, 'flash-message-requester'));
		
		$input = new TextBox('requester-nif');
		$input->setSuggestion(new Seek($search));
		$input->setSpan(2);
		$input->setMask('999.999.999-99');
		$form->buildField('CPF', $input);
		
		$input = new Output('requester-name');
		$form->buildField('Requisitante', $input);
		
		$input = new Output('lotation-description');
		$form->buildField('Órgão de lotação', $input);
		
		$modalTransfer = new Modal('transfer-requester-modal', new Title('Transferir Requisitante', 3));
		$modalTransfer->setWidth(650);
		$modalTransfer->setBody($form);
		$modalTransfer->addButton(new Button('Transferir', new TgFormSubmit($transfer, $form), Button::Primary));
		$modalTransfer->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($modalTransfer);
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary),
			array(new Button('Transferir Requisitante', new TgModalOpen($modalTransfer), Button::Success)),
			array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		
		$table = $this->buildTable('user-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('name', 'Requisitante', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('nif', 'CPF', null, 120);
		$table->buildColumnText('lotation', 'Lotação', null, 300, null, function (AdministrativeUnit $value) {
			return $value->getName();
		});
		$table->buildColumnText('active', 'Status', null, 70, null, function ( $value ) {
			return $value ? new Label('Ativo', Label::Success) : new Label('Inativo', Label::Important);
		});
		if ($showAgencies) {
			$table->buildColumnText('lotation', 'Órgão', null, 80, null, function (AdministrativeUnit $value) {
				return (string) $value->getAgency();
			});
		}
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		$table->buildColumnAction('active', new Icon('icon-remove'), $active, null, function( Button $button, User $user ) {
			$button->setIcon(new Icon($user->getActive() ? 'icon-remove' : 'icon-ok'));
		});
		$table->buildColumnAction('reset', new Icon('icon-asterisk'), $password);
	}
	
}
?>