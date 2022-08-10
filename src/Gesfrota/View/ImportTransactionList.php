<?php
namespace Gesfrota\View;

use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
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
use Gesfrota\Model\Domain\ImportTransaction;

class ImportTransactionList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $upload
	 * @param Action $preProcess
	 * @param Action $listItems
	 * @param Action $download
	 * @param Action $remove
	 * @param array $providers
	 */
	public function __construct( Action $filter, Action $upload, Action $preProcess, Action $listItems, Action $download, Action $remove, array $providers) {
		$this->buildPanel('Entidades Externas', 'Importar Transações de Serviço');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
	    $input = new ComboBox('provider');
	    $input->setSpan(2);
	    $input->setOptions($providers);
	    $form->buildField('Prestador de Serviço', $input);
		
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
		
		$table = $this->buildTable('import-transaction-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('description', 'Descrição', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('dateInitial', 'Período', clone $filter, 150, null, function($value, ImportTransaction $import) {
		    $value = $import->getDatePeriod();
		    return $value[0]->format('d/m/Y') . ' a ' . $value[1]->format('d/m/Y');
		});
		$table->buildColumnText('serviceProvider', 'Prestador de Serviço', clone $filter, 80);
		$table->buildColumnText('amountItems', 'Transações Importadas', null, 50);
		$table->buildColumnText('fileSize', null, null, 70, null, function( $bytes ) {
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
		$confirm->setBody(new Paragraph('Você deseja excluir definitivamente esta Importação?'));
		$confirm->setWidth(350);
		$confirm->addButton(new Button('Ok', new TgModalConfirm(), Button::Primary));
		$confirm->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($confirm);
		
		$table->buildColumnAction('download', new Icon('icon-download-alt'), $download);
		$table->buildColumnAction('pre-process', new Icon('icon-cog'), $preProcess, null, function (Button $btn, ImportTransaction $import) {
		    $btn->setDisabled($import->getFinished());
		});
		$table->buildColumnAction('items-list', new Icon('icon-list'), $listItems);
		$table->buildColumnAction('remove', new Icon('icon-remove'), $remove, $confirm);
		
	}
	
}
?>