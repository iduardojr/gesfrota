<?php
namespace Gesfrota\View;


use Gesfrota\Model\Notice;
use Gesfrota\Model\Domain\User;
use Gesfrota\View\Widget\AbstractList;
use Gesfrota\View\Widget\BuilderTable;
use PHPBootstrap\Widget\Widget;
use PHPBootstrap\Widget\Action\Action;
use PHPBootstrap\Widget\Action\TgLink;
use PHPBootstrap\Widget\Button\Button;
use PHPBootstrap\Widget\Layout\Box;
use PHPBootstrap\Widget\Layout\Row;
use PHPBootstrap\Widget\Misc\Anchor;
use PHPBootstrap\Widget\Misc\Icon;
use PHPBootstrap\Widget\Table\ColumnText;

class AccountNoticesTable extends AbstractList {
	
	/**
	 * @param Action $filter
	 * @param Action $view
	 * @param Action $read
	 * @param User $user
	 * @param Widget $display
	 */
	public function __construct(Action $filter, Action $view, Action $read, User $user, Widget $display) {
		$this->buildPanel('Minha Conta', 'Visualizar Notificações');
		
		$table = new BuilderTable('account-notices-table');
		$table->buildPagination(clone $filter);
		
		$table->buildColumnText('title', 'Assunto', clone $filter, null, ColumnText::Left, function ($title, Notice $notice) use ($user, $view) {
		    $title = $notice->isReadBy($user) ? $title : '<strong>' . $title . '</strong>';
		    $action = clone $view;
		    $action->setParameter('key', $notice->getId());
		    return new Anchor($title, new TgLink($action));
		});
		$table->buildColumnText('updatedAt', 'Atualizado em', clone $filter, 120, null, function ( \DateTime $value ) {
			return $value->format('d/m/Y H:i');
		});
		$table->buildColumnAction('read', new Icon('icon-ok'), $read, null, function (Button $btn, Notice $notice) use ($user) {
		    $btn->setDisabled($notice->isReadBy($user));
		});
		
		$this->component = $table;
		$this->panel->append(new Row(false, [new Box(5, $table), new Box(7, $display)]));
	}
}
?>