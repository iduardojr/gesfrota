<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\Disposal;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\CheckBoxList;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\DateBox;
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
use PHPBootstrap\Widget\Action\TgWindows;
use Gesfrota\Model\Entity;
use Gesfrota\Model\Domain\DisposalLot;

class DisposalList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $remove
	 * @param Action $do
	 * @param \Closure $doClosure
	 * @param Action $print
	 * @param Action $export
	 * @param Action $lot
	 * @param array $showAgencies
	 */
    public function __construct( Action $filter, Action $new, Action $remove, Action $do, \Closure $doClosure, Action $print, Action $export, Action $lot = null, array $showAgencies = null  ) {
		$this->buildPanel('Minha Frota', 'Gerenciar Disposições para Alienação');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		if ($showAgencies) {
			$input = new ComboBox('agency');
			$input->setSpan(2);
			$input->setOptions($showAgencies);
			$form->buildField('Órgão', $input);
		}
		
		$input = new TextBox('description');
		$input->setSpan(5);
		$form->buildField('Descrição', $input);
		
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
		
		if ($lot) {
    		$this->buildToolbar([new Button('Nova', new TgLink($new), Button::Primary)],
    		                    [new Button('Fechar Lote', new TgLink($lot), Button::Success)],
    							[new Button(['Filtrar', new Icon('icon-filter')], new TgModalOpen($modalFilter), [Button::Link, Button::Mini], $btnFilter)]);
		} else {
		    $this->buildToolbar([new Button('Nova', new TgLink($new), Button::Primary)],
		                        [new Button(['Filtrar', new Icon('icon-filter')], new TgModalOpen($modalFilter), [Button::Link, Button::Mini], $btnFilter)]);
		}
		
		$table = $this->buildTable('disposal-list');
		$table->buildPagination(clone $filter);
		
		$table->setContextRow(function (Disposal $object) {
		   return $object instanceof DisposalLot ? 'success' : ''; 
		});
		$table->buildColumnText('lft', '#', clone $filter, 80, null, function ( $value, Entity $object ) {
		    return $object->getCode();
		});
	    $table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left, function ($value, Disposal $object) use ($table, $showAgencies) {
	        if ($table->getDataSource()->getSort() == 'u.lft' && $showAgencies) {
	            return '<div' . ( $object->getParent() && $object->getParent()->getId() > 0 ? ' style="text-indent: 10px;"> |- ' : '>' ) . $value . '</div>';
	        }
	        return $value;
	    });
        $table->buildColumnText('parent', null, null, 50, null, function ($value) {
            return $value && $value->getId() > 0 ? '<small>Lote #'. $value->getId() . '</small>' : '';
        });
		
		if ($showAgencies) {
			$table->buildColumnText('agency', 'Órgão', clone $filter, 70);
		}
		
		$table->buildColumnText('openedAt', 'Aberto em', clone $filter, 150, null, function ($value) {
			return $value->format('d/m/Y H:i');
		});
		
		$table->buildColumnText('status', 'Status', clone $filter, 70, null, function ( $value, Disposal $object ) {
			$status = Disposal::getStatusAllowed();
			$label = new Label($status[$value]);
			switch ( $value ) {
				case Disposal::APPRAISED:
					$label->setStyle(Label::Warning);
					break;
					
				case Disposal::CONFIRMED:
					$label->setStyle(Label::Success);
					break;
				
				case Disposal::DECLINED:
					$label->setStyle(Label::Important);
					break;
					
				case Disposal::FORWARDED:
				    $label->setStyle(Label::Info);
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
		
	   $table->buildColumnAction('print', new Icon('icon-print'), new TgWindows($print, 1024, 762));
	   $table->buildColumnAction('exprot', new Icon('icon-share-alt'), $export);
	}
	
}
?>