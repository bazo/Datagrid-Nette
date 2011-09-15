<?php
namespace Tatami\Components\Datagrid\Filters;
/**
 * FilterObject
 *
 * @author Martin Bažík
 * @package Core
 */
class FilterObject
{
    protected
        $field,

        $operator,

        $value,

        $queryValue,

        $emptyValue = ''
    ;

    public function  __construct($field, $operator, $value, $queryValue)
    {
        $this->field = $field;
        $this->operator = $operator;
        $this->value = $value;
        $this->queryValue = $queryValue;
    }

    public function apply($df)
    {
        return $df->where($this->field.' '.$this->operator, $this->queryValue);
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getEmptyValue()
    {
        return $this->emptyValue;
    }
}