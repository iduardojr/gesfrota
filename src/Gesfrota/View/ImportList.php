<?php
namespace Gesfrota\View;

use Gesfrota\Model\Sys\Import;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\DateBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalConfirm;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;

class ImportList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $upload
	 * @param Action $preProcess
	 * @param Action $transform
	 * @param Action $download
	 * @param Action $remove
	 */
	public function __construct( Action $filter, Action $upload, Action $preProcess, Action $transform, Action $download, Action $remove) {
		$this->buildPanel('Sistema', 'Gerenciar Importações');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('desc');
		$input->setSpan(5);
		$form->buildField('Descrição', $input);
		
		$input = [];
		$input[1] = new DateBox('date-initial', new Date(new DateFormat('dd/mm/yyyy')));
		$input[1]->setSpan(2);
		
		$input[2] = new DateBox('date-final', new Date(new DateFormat('dd/mm/yyyy')));
		$input[2]->setSpan(2);
		$form->buildField('Período', $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$modalFilter->setWidth(750);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(new Button('Importar', new TgLink($upload), Button::Primary),
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('import-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('amountImported', 'Itens Importados', null, 50, null, function ($value, Import $import) {
		    return $value . '/' . $import->getAmountItems();
		});
		$table->buildColumnText('fileSize', null, null, 50, null, function( $bytes ) {
		        $bytes = floatval($bytes);
		        $multiples = [
		            ["UNIT" => "TB", "VALUE" => pow(1024, 4)],
		            ["UNIT" => "GB", "VALUE" => pow(1024, 3)],
		            ["UNIT" => "MB", "VALUE" => pow(1024, 2)],
		            ["UNIT" => "KB", "VALUE" => 1024],
		            ["UNIT" => "B ", "VALUE" => 1],
		        ];
		        
		        foreach($multiples as $multiple) {
		            if($bytes >= $multiple["VALUE"]) {
		                $result = $bytes / $multiple["VALUE"];
		                $result = strval(round($result, 1))." ".$multiple["UNIT"];
		                break;
		            }
		        }
		        return $result;
		});
		
		$confirm = new Modal('modal-remove-confirm', new Title('Confirme', 3));
		$confirm->setBody(new Paragraph('Você deseja excluir definitivamente esta Disposição?'));
		$confirm->setWidth(350);
		$confirm->addButton(new Button('Ok', new TgModalConfirm(), Button::Primary));
		$confirm->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($confirm);
		
		$table->buildColumnAction('download', new Icon('icon-download-alt'), $download);
		$table->buildColumnAction('do', new Icon('icon-stop'), $preProcess, null, function (Button $btn, Import $import) use ($preProcess, $transform) {
		    switch ($import->getStatus()) {
		        case Import::UPLOADED:
		            $action = clone $preProcess;
		            $action->setParameter('key', $import->getId());
		            $btn->setIcon(new Icon('icon-random'));
		            $btn->setToggle(new TgLink($action));
		            break;
		          
		        case Import::PREPROCESSED: 
		            $action = clone $transform;
		            $action->setParameter('key', $import->getId());
		            $btn->setIcon(new Icon('icon-cog'));
		            $btn->setToggle(new TgLink($action));
		            break;
		            
		        case Import::FINISHED: 
		            $btn->setDisabled(true);
		            break;
		    } 
		});
		$table->buildColumnAction('remove', new Icon('icon-remove'), $remove, $confirm);
		
	}
	
}
?>