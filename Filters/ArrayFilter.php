<?php
namespace Tatami\Components\Datagrid\Filters;
/**
 * ArrayFilter
 *
 * @author Martin Bažík
 * @package Core
 */
class ArrayFilter extends BaseFilter
{
    protected $items = array();

    public function  __construct(IComponentContainer $parent = NULL, $name = NULL, $items = array())
    {
        parent::__construct($parent, $name);
        $this->items = $items;
    }


    public function getFormControl()
    {
        $selectBox = new SelectBox($this->name, $this->items);
        $selectBox->setAttribute("style", "width:120px;");
        return $selectBox;
    }

    public function getFilter(&$value)
    {
        if(\is_numeric($value)) return new FilterObject($this->parent->name, '= %i', (int)$value, (int)$value);
        return new FilterObject($this->parent->name, '= %s', $value, $value);
    }

    public function getItems()
    {
        return $this->items;
    }

    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }
}