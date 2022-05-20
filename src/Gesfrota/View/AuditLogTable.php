<?php
namespace Gesfrota\View;

use Gesfrota\Util\Format;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderForm;
use PHPBootstrap\Format\DateFormat;
use PHPBootstrap\Format\TimeFormat;
use PHPBootstrap\Validate\Pattern\Date;
use PHPBootstrap\Validate\Pattern\Time;
use PHPBootstrap\Validate\Required\Required;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Form\Controls\ComboBox;
use PHPBootstrap\Widget\Form\Controls\DateBox;
use PHPBootstrap\Widget\Form\Controls\TextBox;
use PHPBootstrap\Widget\Form\Controls\TimeBox;
use PHPBootstrap\Widget\Misc\Badge;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Modal\TgModalOpen;
use PHPBootstrap\Widget\Table\ColumnText;
use Gesfrota\Services\Log;

class AuditLogTable extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $view
	 * @param array $optClass
	 */
	public function __construct( Action $filter, Action $view, array $optClasses = [] ) {
		$this->buildPanel('Segurança', 'Auditória');
		
		$reset = clone $filter;
		$reset->setParameter('reset', 1);
		
		$form = new BuilderForm('form-filter');
		
		$input = new TextBox('id');
		$input->setSpan(2);
		$form->buildField('#', $input);
		
		$input = new TextBox('uri');
		$input->setSpan(4);
		$form->buildField('URI', $input);
		
		$input = new TextBox('agency');
		$input->setSpan(4);
		$form->buildField('Órgão', $input);
		
		$input = new TextBox('user');
		$input->setSpan(4);
		$form->buildField('Usuário', $input);
		
		$inputs = [];
		$input = new ComboBox('object-class');
		$input->setSpan(3);
		$input->addOption('', 'Todos');
		foreach($optClasses as $value) {
			$input->addOption($value, str_replace('Gesfrota\\Model\\Domain\\', '', $value));
		}
		$inputs[] = $input;
		
		$input = new TextBox('object-id');
		$input->setPlaceholder('ID');
		$input->setSpan(1);
		//$input->setRequired(new Required($inputs[0], 'Por favor, selecione a classe do objeto'));
		$inputs[] = $input;
		
		
		
		$form->buildField('Objeto', $inputs);
		
		$inputs = [];
		$input = new DateBox('date-initial', new Date(new DateFormat('dd/mm/yyyy')));
		$input->setSpan(2);
		$inputs[] = $input;
		
		$input = new TimeBox('time-initial', new Time(new TimeFormat('HH:mm')));
		$input->setSpan(1);
		$input->setText('00:00');
		$inputs[] = $input;
		$form->buildField('Data Inicial', $inputs);
		
		$inputs = [];
		$input = new DateBox('date-final', new Date(new DateFormat('dd/mm/yyyy')));
		$input->setSpan(2);
		$inputs[] = $input;
		
		$input = new TimeBox('time-final', new Time(new TimeFormat('HH:mm')));
		$input->setSpan(1);
		$input->setText('23:59');
		$inputs[] = $input;
		$form->buildField('Data Final', $inputs);
		
		$modalFilter = $this->buildFilter($form, $filter, $reset);
		$btnFilter = new Button(['Remover Filtros', new Icon('icon-remove')], new TgLink($reset), [Button::Link, Button::Mini]);
		$btnFilter->setName('remove-filter');
		
		$this->buildToolbar([new Button(['Filtrar', new Icon('icon-filter')], new TgModalOpen($modalFilter), [Button::Link, Button::Mini]), $btnFilter]);
		
		$table = $this->buildTable('user-list');
		$table->buildPagination(clone $filter);
		
		$table = $this->buildTable('audit-log-table');
		
		$table->buildPagination($filter);
		
		$table->buildColumnText('id', '#', clone $filter, 80, null, function($value) {
			return Format::code($value, 6);
		});
		$table->buildColumnText('referer', 'URI', clone $filter, null, ColumnText::Left);
		$table->buildColumnText('instance', null, null, null, ColumnText::Right, function($value) {
			return new Badge(str_replace('Gesfrota\\Model\\Domain\\', '', $value));
		});
		$table->buildColumnText('user', 'Usuário', clone $filter, 200, null, function ( $value ) {
			return (string) $value;
		});
		$table->buildColumnText('agency', 'Órgão', clone $filter, 100, null, function ( $value ) {
			return $value->getAcronym() ;
		});
		$table->buildColumnText('created', 'Data', clone $filter, 150, null, function ( $value ) {
			return $value->format('d/m/Y H:i:s');
		});
		$table->buildColumnAction('view', new Icon('icon-list-alt'), $view);
			
	}
}
?>