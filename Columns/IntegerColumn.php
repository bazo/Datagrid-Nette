<?php
namespace Tatami\Components\Datagrid\Columns;
/**
 * Description of IntegerColumn
 *
 * @author Martin
 */
class IntegerColumn extends BaseColumn
{
    public function setRecord($value, $record)
    {
        $this->value = (int)$value;
        $this->record = $record;
        return $this;
    }
}
?>
