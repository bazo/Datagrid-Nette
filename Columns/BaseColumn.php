<?php
namespace Tatami\Components\Datagrid\Columns;
use Nette\Application\UI\Control;
/**
 * Description of BaseColumn
 *
 * @author Martin
 */
abstract class BaseColumn extends Control implements IDatagridColumn
{
    protected 
        $record,
        $value,
        $alias,
        $hasFilter = false,
        $filterType,
        $defaultFilterType = 'string',
        $type,
        $editMode = false,
        $editable = false
    ;

    public
        $onCellRender = array(),
        $hidden = false
    ;

    public function  __construct(IComponentContainer $parent = NULL, $name = NULL)
    {
        parent::__construct($parent, $name);
        if($this->parent->getInlineEditEnabled() == true) $this->editable = true;
    }

    /**
     *
     * @param mixed $value
     * @param DibiRow $record
     * @return BaseColumn
     */
    public function setRecord($value, $record)
    {
        $this->value = $value;
        $this->record = $record;
        return $this;
    }

    public function getType() {
        return $this->type;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getValue()
    {
        $this->value = $this->formatValue($this->value);
        $this->onCellRender();
        return $this->value;
    }

    /**
     *
     * @param mixed $value
     * @return mixed
     */
    protected function formatValue($value)
    {
        return $value;
    }

    /**
     * changes the value of the field according to the callback
     */
    protected function onCellRender()
    {
        foreach($this->onCellRender as $function)
        {
            $this->value = $function($this->value, $this->record);
        }
    }

    
    public function renderHeader()
    {
        if($this->alias != null) return $this->alias;
        else return $this->name;
    }

    public function hasFilter()
    {
        return $this->hasFilter;
    }

    /**
     * sets DefaultFilter for the column type
     */
    public function setDefaultFilter()
    {
        $filterType = $this->filterType != null ? $this->filterType : $this->defaultFilterType;
        $this->setFilter($filterType);
    }

    /**
     *
     * @param string $type
     * @return DibiDatagrid_TextFilter
     */
    public function setFilter($type)
    {
        $this->hasFilter = true;
        $this->parent->hasFilters = true;
        switch($type)
        {
            case 'text':
                return new TextFilter($this, 'filter');
            break;

            case 'DateRangeFilter':
                return new DateRangeFilter($this, 'filter');
            break;

            case 'array':
                return new ArrayFilter($this, 'filter');
            break;
	
	    case 'null':
                return new NullFilter($this, 'filter');
            break;

            default :
                return new TextFilter($this, 'filter');//$this->setTextFilter();
            break;
        }
    }

    public function getFilter()
    {
        return $this->getComponent('filter')->getFormControl();
    }

    protected function beforeSetFilter()
    {
        $this->hasFilter = true;
        $this->parent->hasFilters = true;
    }

    public function setTextFilter()
    {
        $this->beforeSetFilter();
        $this->filterType = 'text';
        return new DibiDatagrid_TextFilter($this, 'filter');
    }
    
    /**
     * modified by Vahram added a default wildcast value at the end
     */
    public function setArrayFilter($items = array(), $strict = false)
    {
        if(!$strict && !array_key_exists('', $items))
        {
	    $items = array('' => '-') + $items;    
	}
        
        $this->beforeSetFilter();
        $this->filterType = 'array';
        return new DibiDatagrid_ArrayFilter($this, 'filter', $items);
    }

    public function getAlias()
    {
        return $this->alias;
    }

    /**
     *
     * @param string $alias
     * @return BaseColumn
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
        return $this;
    }

    /**
     *
     * @param Callback $callback
     * @return BaseColumn
     */
    public function addOnCellRender(Callback $callback)
    {
        $this->onCellRender[] = $callback;
        return $this;
    }

    public function render()
    {
        if($this->editMode == true)
        {
            $html = Html::el('span')->class('inline-edit');
            $html->add($this->getEditModeHtml()->class('in_edit'));
            $linkSave = Html::el('a')->href($this->link('saveEdit!'))->add(Html::el('span')->class('datagrid-icon datagrid-icon-cross save-editable'));
            $linkCancel = Html::el('a')->href($this->link('CancelEdit!'))->add(Html::el('span')->class('datagrid-icon datagrid-button-apply cancel-editable'));
            $html->add($linkSave);
            $html->add($linkCancel);
        }
        else
        {
            $value = $this->getValue();
            $html = Html::el('td')->class('cell '.$this->type.' '.$this->name);
            if($value instanceof Html)
            {
                $html->add($value);
            }
             
            else if($value == null)
            {
                $html->setText('');
            }
            else
            {
                $html->setText($value);
            }

            if($this->editable == true)
            {
                $html->class = $html->class.' editable';
                $html->{'data-edit'} = 'bleh';
                $link = Html::el('a')->href($this->link('makeEditable!'))->setText('Edit')->class('ajax');
                $html->add($link);
            }
        }
        echo $html;
    }

    protected function getEditModeHtml()
    {
        return Html::el('input')->type('text')->name($this->name)->value($this->value);
    }

    public function handleMakeEditable()
    {
        $this->editMode = true;
        $this->parent->invalidateControl();
    }

    public function handleSaveEdit($value)
    {
        
    }
    
    public function filterByOtherFieldArray($field, $items)
    {
	$items = array('' => '-') + $items;    
        $this->beforeSetFilter();
        $this->filterType = 'array';
        return new ForeignFieldArrayFilter($this, 'filter', $items, $field);
    }
}
