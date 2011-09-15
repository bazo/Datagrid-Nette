<?php
namespace Tatami\Components\Datagrid\Actions;
use Nette\ComponentModel\Component,    Nette\Utils\Html;
/**
 * Description of DibiDatagridAction
 *
 * @author Martin
 */
class Action extends Component
{
    protected
        $title,
        $destination,
        $key = 'id',
        $value,
        $record,
        $showTitle = true,
        $ajax,
        $icon,
        $params = array(),
        $dynamicParams = array()
    ;

    public 
        $onActionRender = array()
    ;

    /**
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     *
     * @param string $title
     * @return DoctrineDatagrid_Action
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     *
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     *
     * @param string $destination
     * @return DoctrineDatagrid_Action
     */
    public function setDestination($destination)
    {
        $this->destination = $destination;
        return $this;
    }

    /**
     *
     * @return string Action key field name 
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     *
     * @param string $key
     * @return DoctrineDatagrid_Action
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     *
     * @return DoctrineDatagrid_Action
     */
    public function showTitle()
    {
        $this->showTitle = true;
        return $this;
    }

    /**
     *
     * @return DoctrineDatagrid_Action 
     */
    public function hideTitle()
    {
        $this->showTitle = false;
        return $this;
    }

    /**
     *
     * @param bool $value
     * @return DoctrineDatagrid_Action
     */
    public function setAjax($value)
    {
        $this->ajax = $value;
        return $this;
    }

    protected function fillParams()
    {
        
        foreach($this->dynamicParams as $param => $field)
        {
            if(isset($this->record->$field))
                @$this->params[$param] = $this->record->$field;
        }
    }

    public function render()
    {
        $this->fillParams();
        $output = '';
        if($this->showTitle == true) $title = $this->title; else $title = '';
        if(empty($this->onActionRender))
        {
            $presenter = Environment::getApplication()->getPresenter();
            $icon = $this->icon != null ? $this->icon : $this->title;
            $output = Html::el('a')->add(Html::el('span')->class(sprintf('icon icon-%s', String::lower($icon))))
                       ->href($presenter->link($this->destination, array($this->key => $this->value) + $this->params))->title($this->title)
                       ->add($title)
                      ;//->setText($title);
        }
        else
        {
            foreach ($this->onActionRender as $function)
            {
                $output .= $function($this->value, $this->record);
            }
        }
        if($this->ajax) $output->addClass('ajax');
        return $output;
    }
    /**
     *
     * @param mixed $value
     * @param DibiRow $record
     * @return DibiDatagrid_Action
     */
    public function setRecord($value, DibiRow $record)
    {
        $this->value = $value;
        $this->record = $record;
        return $this;
    }

    /**
     * Returns shown title
     * @return string
     */
    public function getShowTitle()
    {
        return $this->showTitle;
    }

    /**
     * Sets the title that will be shown on action
     * @param string $showTitle
     * @return DibiDatagrid_Action
     */
    public function setShowTitle($showTitle)
    {
        $this->showTitle = $showTitle;
	return $this;
    }

    /**
     * Return the name of icon
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets icon
     * @param string $icon
     * @return DibiDatagrid_Action
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
	return $this;
    }

    /**
     * Adds dynamic parameter
     * @param string $paramName
     * @param string $field
     * @return DibiDatagrid_Action
     */
    public function addParam($paramName, $field = null)
    {
        $field = $field == null ? $paramName : $field;
        $this->dynamicParams[$paramName] = $field;
        return $this;
    }

    /**
     * Adds static variable
     * @param string $variable name
     * @param string $value value
     * @return DibiDatagrid_Action
     */
    public function addVariable($variable, $value = null)
    {
        @$this->params[$variable] = $value;
	return $this;
    }

}