<?php
namespace Tatami\Components\Datagrid\Columns;
/**
 * Description of IntegerColumn
 *
 * @author Martin
 */
class FloatColumn extends BaseColumn
{
    protected function formatValue($value)
    {
        return sprintf('%0.2f', $value);
    }
}