<?php
namespace Tatami\Components\Datagrid\Columns;
/**
 * Description of IDatagridColumn
 *
 * @author Martin
 */
interface IDatagridColumn
{
    public function setRecord($value, $record);

    public function getValue();
}