<?php
namespace Tatami\Components\Datagrid\Filters;
/**
 * DataRangeFilterObject
 *
 * @author Martin Bažík
 * @package Core
 */
class DateRangeFilterObject extends FilterObject
{
    protected
        $from,
        $to,

        $emptyValue = array('from' => '', 'to' => '')
    ;

    public function  __construct($field, $value, $from, $to)
    {
        $this->field = $field;
        $this->value = $value;
        $this->from = $from;
        $this->to = $to;
    }

    public function apply($df)
    {
        return $df->where('%i <= UNIX_TIMESTAMP(%sql) AND UNIX_TIMESTAMP(%sql) <= %i ', $this->from, $this->field, $this->field, $this->to);
    }

    public function getValue()
    {
        return $this->value;
    }
}