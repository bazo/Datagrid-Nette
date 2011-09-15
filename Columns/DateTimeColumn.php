<?php
namespace Tatami\Components\Datagrid\Columns;
/**
 * DateTimeColumn
 *
 * @author Martin Bažík
 * @package Core
 */
class DateTimeColumn extends BaseColumn
{
    protected $defaultFilterType = 'DateRangeFilter';

    /**
     *
     * @param DibiDateTime $value
     * @return string
     */
    protected function formatValue($value)
    {
        if($value == null) return "";
        if($value instanceof DibiDateTime)
            return $value->format('d.m.Y');
        else
            return date('d.m.Y', strtotime($value));
    }
}
