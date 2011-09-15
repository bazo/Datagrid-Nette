<?php
namespace Tatami\Components\Datagrid\Columns;
use Nette\Application\UI\Control;
/**
 * Description of ActionColumn
 *
 * @author Martin
 */
class ActionColumn extends Control implements IActionColumn
{
    protected $record, $value;

    /**
     *
     * @param mixed $value
     * @param DibiRow $record
     * @return ActionColumn
     */
    public function setRecord($value, $record)
    {
        $this->value = $value;
        $this->record = $record;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function renderHead()
    {
        return $this->name;
    }

    /**
     * Adds normal action
     * @param string $title
     * @param string $destination
     * @param bool $ajax
     * @return DibiDatagrid_Action
     */
    public function addAction($title, $destination, $ajax = false)
    {
        $title = String::lower($title);
        $action = new Action($this, $title);
        $action->setTitle($title);
        $action->setDestination($destination);
        $action->setAjax($ajax);
        return $action;
    }

    /**
     * Adds dynamic action
     * @param string $title
     * @param bool $ajax
     * @return DibiDatagrid_DynamicAction
     */
    public function addDynamicAction($title, $ajax = false)
    {
        $title = String::lower($title);
        $action = new DynamicAction($this, $title);
        $action->setTitle($title);
        $action->setAjax($ajax);
        return $action;
    }

    /**
     * gets action
     * @param string $action
     * @return DibiDatagrid_Action
     */
    public function getAction($action)
    {
        $action = String::lower($action);
        return $this->getComponent($action);
    }

    /**
     * hides action
     * @param string $action
     * @return DibiDatagrid_ActionColumn
     */
    public function hideAction($action)
    {
        $action = String::lower($action);
        $this->removeComponent($this->getComponent($action));
        return $this;
    }

    /**
     * Gets all actions
     * @return array
     */
    public function getActions()
    {
        return $this->getComponents(false, 'DibiDatagrid_Action');
    }
}