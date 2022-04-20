<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Disposal;
use Gesfrota\Model\Domain\Vehicle;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Action\TgWindows;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\CheckBox;
use PHPBootstrap\Widget\Form\Controls\CheckBoxList;
use PHPBootstrap\Widget\Form\Controls\Output;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalConfirm;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;
use PHPBootstrap\Widget\Misc\Anchor;
use PHPBootstrap\Widget\Form\Controls\DateBox;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Format\DateFormat;

class DisposalList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $remove
	 * @param Action $do
	 * @param \Closure $doClosure
	 * @param Action $print
	 * @param boolean $showAgency
	 */
	public function __construct( Action $filter, Action $new, Action $remove, Action $do, \Closure $doClosure, Action $print, $showAgency = false  ) {
		$this->buildPanel('Minha Frota', 'Gerenciar Disposições para Alienação');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('description');
		$input->setSpan(5);
		$form->buildField('Nome da Lista', $input);
		
		$input = [];
		$input[1] = new DateBox('date-initial', new Date(new DateFormat('dd/mm/yyyy')));
		$input[1]->setSpan(2);
		
		$input[2] = new DateBox('date-final', new Date(new DateFormat('dd/mm/yyyy')));
		$input[2]->setSpan(2);
		$form->buildField('Período', $input);
		
		$input = new CheckBoxList('status', true);
		$input->setOptions(Disposal::getStatusAllowed());
		$form->buildField('Status', $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$modalFilter->setWidth(750);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(array(new Button('Novo', new TgLink($new), Button::Primary)),
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('disposal-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('description', 'Nome da Lista', clone $filter, null, ColumnText::Left);
		
		if ($showAgency) {
			$table->buildColumnText('requesterUnit', 'Órgão', clone $filter, 70);
		}
		
		$table->buildColumnText('requestedAt', 'Aberto em', clone $filter, 150, null, function ($value) {
			return $value->format('d/m/Y H:i');
		});
		
		$table->buildColumnText('status', 'Status', clone $filter, 70, null, function ( $value, Disposal $object ) {
			$status = Disposal::getStatusAllowed();
			$label = new Label($status[$value]);
			switch ( $value ) {
				case Disposal::REQUESTED:
					$label->setStyle(Label::Warning);
					break;
					
				case Disposal::CONFIRMED:
					$label->setStyle(Label::Success);
					break;
				
				case Disposal::DECLINED:
					$label->setStyle(Label::Important);
					
					break;
			}
			return $label;
		});
		
		$table->buildColumnAction('do', new Icon('icon-pencil'), $do, null, $doClosure);
			
		$confirm = new Modal('disposal-confirm-modal', new Title('Confirme', 3));
		$confirm->setBody(new Paragraph('Você deseja excluir definitivamente esta Disposição?'));
		$confirm->setWidth(350);
		$confirm->addButton(new Button('Ok', new TgModalConfirm(), Button::Primary));
		$confirm->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($confirm);
	
		$table->buildColumnAction('remove', new Icon('icon-remove'), $remove, $confirm, function (Button $btn, Disposal $obj) {
		    if (! $obj->getStatus() == Disposal::DRAFTED) {
		        $btn->setDisabled(true);
		        $btn->setToggle(null);
		    }
		});
		
		$table->buildColumnAction('print', new Icon('icon-print'), $print, null, function (Button $btn, Disposal $obj) {
			if ( $obj->getStatus() == Disposal::DRAFTED) {
				$btn->setDisabled(true);
			}
		});
	}
	
}
?>