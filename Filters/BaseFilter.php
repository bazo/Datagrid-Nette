<?php
namespace Tatami\Components\Datagrid\Filters;
use \Nette\Application\UI\Control, \Nette\Forms\Controls;
/**
 * BaseFilter
 *
 * @author Martin Bažík
 * @package Core
 */
abstract class BaseFilter extends Control implements IFilter
{
    public function render()
    {
        return $this->name;
    }

    public function getFormControl()
    {
        return new TextInput($this->name);
    }

    public function apply(&$dql, $value)
    {
        $dql->where($this->name.' like %?%', $value);
    }

    public function getFilter(&$value)
    {
        return new FilterObject($this->parent->name, 'like %s', $value, '%'.$value.'%');
    }


}