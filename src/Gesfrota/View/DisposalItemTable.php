<?php
namespace Gesfrota\View;

use Gesfrota\Model\Domain\DisposalItem;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgAjax;
use PHPBootstrap\Widget\Action\TgWindows;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;
use Gesfrota\Model\Domain\Disposal;
use PHPBootstrap\Widget\Form\Controls\Uneditable;
use PHPBootstrap\Widget\Layout\Box;
use Gesfrota\Model\Domain\DisposalLot;
use Gesfrota\View\Widget\AbstractForm;
use PHPBootstrap\Widget\Widget;
use PHPBootstrap\Widget\Form\Controls\Label;
use PHPBootstrap\Widget\Form\Controls\ControlGroup;
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Widget\Form\Controls\Hidden;
use PHPBootstrap\Validate\Required\EqualTo;
use PHPBootstrap\Validate\Measure\Min;
use PHPBootstrap\Widget\Form\Controls\Decorator\InputContext;
use PHPBootstrap\Widget\Form\Form;

class DisposalItemTable extends BuilderTable {
	
	/**
	 * @param Action $survey
	 * @param Action $remove
	 * @param Action $print
	 * @param boolean $showAgency
	 */
    public function __construct(Action $survey = null, Action $remove = null, Action $print = null, $showAgency = false) {
		parent::__construct('disposal-items-table');

		$this->buildColumnText('code', '#', null, 80);
		$this->buildColumnText('description', 'Ativo', null, 450, ColumnText::Left);
		if ($showAgency) {
    		$this->buildColumnText('disposal', 'Órgão', null, 80, null, function (Disposal $value) {
    		   return $value->getAgency()->getAcronym(); 
    		});
		}
		$this->buildColumnText('classification', 'Classificação', null, 75, null, function ( $value ) {
			if ($value > 0) {
				return DisposalItem::getClassificationAllowed()[$value];
			}
			return '-';
		});
		
	    $this->buildColumnText('conservation', 'Conservação', null, 100, null, function ($value){
	        if ($value > 0) {
	            return DisposalItem::getConservationAllowed()[$value];
	        }
	        return '-';
	    });
		
		$this->buildColumnText('value', 'Valor', null, null, null, function ( $value ) {
			if ($value > 0) {
				return 'R$ ' . number_format($value, 2, ',', '.');
			}
			return '-';
		});
		
		$this->buildColumnText('debit', 'Débitos', null, null, null, function ( $value ) {
			if ($value > 0) {
				return 'R$ ' . number_format($value, 2, ',', '.');
			}
			return '-';
		});
		
		if ($survey) {
		    $this->buildColumnAction('survey', new Icon('icon-list-alt'), $survey);
		}
		if ($remove) {
		    $this->buildColumnAction('remove', new Icon('icon-remove'), new TgAjax($remove, 'flash-message', TgAjax::Json));
		}
		if ($print) {
		    $this->buildColumnAction('print', new Icon('icon-print'), new TgWindows($print, 1024, 720));
		}
		
	}
	
	/**
	 * Atribui rodape
	 *
	 * @param Widget|Disposal $footer
	 */
	public function setFooter($footer) {
	    if ($footer instanceof Disposal) {
	        $disposal = $footer;
	        $footer1 = new Box(2);
	        $footer2 = new Box(10);
	        
	        $input= new Uneditable('assets-count');
	        $input->setSpan(2);
	        if ( $disposal->getAppraisedAt() || $disposal instanceof DisposalLot) {
	            $input->setValue($disposal->getAmountAssets());
	            $label= 'Total de Ativos';
	        } else {
	            $input->setValue($disposal->getAmountAssetsAppraise() . ' / ' . $disposal->getAmountAssets());
	            $label= 'Ativos Avaliados';
	            $footer2->setSpan(8);
	        }
	        $footer1->append(new ControlGroup(new Label($label), $input));
	        
	        $input = new Uneditable('assets-value');
	        $input->setSpan(2);
	        $input->setValue('R$ ' . number_format($disposal->getTotalValue(), 2, ',', '.'));
	        $footer1->append(new ControlGroup(new Label('Arrecadação Estimada'), $input));
	        
	        $input = new Uneditable('assets-debit');
	        $input->setSpan(2);
	        $input->setValue('R$ ' . number_format($disposal->getTotalDebit(), 2, ',', '.'));
	        $footer1->append(new ControlGroup(new Label('Total de Débitos'), $input));
	        
	        if ( ! $disposal instanceof DisposalLot ) {
	            if ($disposal->getAppraisedAt() ) {
	                $input = new Uneditable('agency');
	                $input->setSpan(2);
	                $input->setValue($disposal->getAgency()->getAcronym());
	                $footer2->append(new ControlGroup(new Label('Disponibilizado Por'), $input));
	                
    	            $row = new Row(true);
    	            
    	            $box = new Box(8);
    	            $input = [];
    	            $input[0] = new Uneditable('appraised-by-nif');
    	            $input[0]->setSpan(2);
    	            $input[0]->setValue($disposal->getAppraisedBy()->getNif());
    	            
    	            $input[1] = new Uneditable('appraised-by-name');
    	            $input[1]->setSpan(4);
    	            $input[1]->setValue($disposal->getAppraisedBy()->getName());
    	            
    	            $box->append(new ControlGroup(new Label('Avaliador Responsável'), $input));
    	            $row->append($box);
    	            
    	            $box = new Box(3);
    	            $input = new Uneditable('appraised-at');
    	            $input->setSpan(2);
    	            $input->setValue($disposal->getAppraisedAt()->format('d/m/Y H:i:s'));
    	            $box->append(new ControlGroup(new Label('Avaliado em'), $input));
    	            $row->append($box);
    	            
    	            $footer2->append($row);
	            } 
	            
	            if ($disposal->getConfirmedAt() ) {
	                $row = new Row(true);
	                
	                $box = new Box(8);
	                $input = [];
	                $input[0] = new Uneditable('confirmed-by-nif');
	                $input[0]->setSpan(2);
	                $input[0]->setValue($disposal->getConfirmedBy()->getNif());
	                
	                $input[1] = new Uneditable('confirmed-by-name');
	                $input[1]->setSpan(4);
	                $input[1]->setValue($disposal->getConfirmedBy()->getName());
	                
	                $box->append(new ControlGroup(new Label('Confirmado Por'), $input));
	                $row->append($box);
	                
	                $box = new Box(3);
	                $input = new Uneditable('confirmed-at');
	                $input->setSpan(2);
	                $input->setValue($disposal->getConfirmedAt()->format('d/m/Y H:i:s'));
	                $box->append(new ControlGroup(new Label('Confirmado em'), $input));
	                $row->append($box);
	                
	                $footer2->append($row);
	            }
	            
	            if ($disposal->getDeclinedAt() ) {
	                $row = new Row(true);
	                
	                $box = new Box(8);
	                $input = [];
	                $input[0] = new Uneditable('declined-by-nif');
	                $input[0]->setSpan(2);
	                $input[0]->setValue($disposal->getDeclinedBy()->getNif());
	                
	                $input[1] = new Uneditable('declined-by-name');
	                $input[1]->setSpan(4);
	                $input[1]->setValue($disposal->getDeclinedBy()->getName());
	                
	                $box->append(new ControlGroup(new Label('Recusado Por'), $input));
	                $row->append($box);
	                
	                $box = new Box(3);
	                $input = new Uneditable('declined-at');
	                $input->setSpan(2);
	                $input->setValue($disposal->getDeclinedAt()->format('d/m/Y H:i:s'));
	                $box->append(new ControlGroup(new Label('Recusado em'), $input));
	                $row->append($box);
	                
	                $footer2->append($row);
	                
	                $input = new Uneditable('declined-jusfify');
	                $input->setSpan(8);
	                $input->setValue(nl2br($disposal->getJustify()));
	                $footer2->append(new ControlGroup(new Label('Motivo da Recusa'), $input));
	            }
	            
	            if ($disposal->getForwardedAt() ) {
	                $row = new Row(true);
	                
	                $box = new Box(8);
	                $input = [];
	                $input[0] = new Uneditable('forwarded-by-nif');
	                $input[0]->setSpan(2);
	                $input[0]->setValue($disposal->getForwardedBy()->getNif());
	                
	                $input[1] = new Uneditable('forwarded-by-name');
	                $input[1]->setSpan(4);
	                $input[1]->setValue($disposal->getForwardedBy()->getName());
	                
	                $box->append(new ControlGroup(new Label('Encaminhado Por'), $input));
	                $row->append($box);
	                
	                $box = new Box(3);
	                $input = new Uneditable('forwarded-at');
	                $input->setSpan(2);
	                $input->setValue($disposal->getForwardedAt()->format('d/m/Y H:i:s'));
	                $box->append(new ControlGroup(new Label('Encaminhado em'), $input));
	                $row->append($box);
	                
	                $footer2->append($row);
	            }
	            
	        } else {
	            $row = new Row(true);
	            
	            $box = new Box(8);
	            $input = [];
	            $input[0] = new Uneditable('forwarded-by-nif');
	            $input[0]->setSpan(2);
	            $input[0]->setValue($disposal->getForwardedBy()->getNif());
	            
	            $input[1] = new Uneditable('forwarded-by-name');
	            $input[1]->setSpan(4);
	            $input[1]->setValue($disposal->getForwardedBy()->getName());
	            
	            $box->append(new ControlGroup(new Label('Encaminhado Por'), $input));
	            $row->append($box);
	            
	            $box = new Box(3);
	            $input = new Uneditable('forwarded-at');
	            $input->setSpan(2);
	            $input->setValue($disposal->getForwardedAt()->format('d/m/Y H:i:s'));
	            $box->append(new ControlGroup(new Label('Encaminhado em'), $input));
	            $row->append($box);
	            
	            $footer2->append($row);
	            
	        }
	        
	        $footer = new Row(true, [$footer2, $footer1]);
	    } 
	    parent::setFooter($footer);
	}
}
?>