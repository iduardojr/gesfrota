<?php
namespace Gesfrota\View;

use Gesfrota\Model\Sys\Notice;
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
use PHPBootstrap\Widget\Misc\Label;
use PHPBootstrap\Widget\Misc\Paragraph;
use PHPBootstrap\Widget\Misc\Title;
use PHPBootstrap\Widget\Modal\Modal;
use PHPBootstrap\Widget\Modal\TgModalClose;
use PHPBootstrap\Widget\Modal\TgModalConfirm;
use PHPBootstrap\Widget\Modal\TgModalLoad;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;

class NoticeList extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $new
	 * @param Action $edit
	 * @param Action $remove
	 * @param Action $views
	 */
	public function __construct( Action $filter, Action $new, Action $edit, Action $remove, Action $views) {
		$this->buildPanel('Comunicação', 'Gerenciar Notificações');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('terms');
		$input->setSpan(5);
		$form->buildField('Termos', $input);
		
		$input = [];
		$input[1] = new DateBox('date-initial', new Date(new DateFormat('dd/mm/yyyy')));
		$input[1]->setSpan(2);
		
		$input[2] = new DateBox('date-final', new Date(new DateFormat('dd/mm/yyyy')));
		$input[2]->setSpan(2);
		$form->buildField('Período', $input);
		
		$input = new ComboBox('status');
		$input->setSpan(2);
		$input->addOption(0, 'Todos');
		$input->addOption(1, 'Publicados');
		$input->addOption(-1, 'Não Publicados');
		$form->buildField('Status', $input);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$modalFilter->setWidth(750);
		$btnFilter = new Button(array('Remover Filtros', new Icon('icon-remove')), new TgLink($reset), array(Button::Link, Button::Mini));
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar(new Button('Novo', new TgLink($new), Button::Primary),
							array(new Button(array('Filtrar', new Icon('icon-filter')), new TgModalOpen($modalFilter), array(Button::Link, Button::Mini)), $btnFilter));
		
		$table = $this->buildTable('notice-list');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnTextId(null, clone $filter);
		$table->buildColumnText('title', 'Título', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('body', null, null, 500, ColumnText::Left, function( $text ) {
		    $maxPos = 70; 
		    $text = strip_tags($text, '<strong>');
		    if (strlen(strip_tags($text)) > $maxPos) {
		        $lastPos = ($maxPos - 3) - strlen(strip_tags($text));
		        $text = substr($text, 0, strrpos($text, ' ', $lastPos)) . '...';
		    }
		    return $text;
		});
		$table->buildColumnText('active', 'Status', clone $filter, 70, null, function ($value) {
		    return $value ? new Label('Publicado', Label::Success) : new Label('Não Publicado', Label::Important);
		});
		$table->buildColumnText('createdAt', 'Criado em', clone $filter, 90, null, function (\DateTime $value) {
	        return $value->format('d/m/Y H:i:s');
	    });
		$table->buildColumnText('readAmount', 'Views⁣', null, 50);
		
		
		$confirm = new Modal('modal-remove-confirm', new Title('Confirme', 3));
		$confirm->setBody(new Paragraph('Você deseja excluir definitivamente esta Disposição?'));
		$confirm->setWidth(350);
		$confirm->addButton(new Button('Ok', new TgModalConfirm(), Button::Primary));
		$confirm->addButton(new Button('Cancelar', new TgModalClose()));
		$this->panel->append($confirm);
		
		$modal = new Modal('modal-views-read', new Title('Lido por', 3));
		$modal->setWidth(850);
		$this->panel->append($modal);
		
		$table->buildColumnAction('edit', new Icon('icon-pencil'), $edit);
		
		$table->buildColumnAction('remove', new Icon('icon-remove'), $remove, $confirm, function( Button $button, Notice $obj ) {
		    $button->setDisabled(!$obj->canDelete());
		});
		
		$table->buildColumnAction('views', new Icon('icon-eye-open'), new TgModalLoad($views, $modal));
	}
	
}
?>