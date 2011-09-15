<?php
namespace Tatami\Components\Datagrid\Filters;
use Tatami\Components\Datagrid\Forms\Controls;
/**
 * NullFilter
 *
 * @author Martin Bažík
 */
class NullFilter extends BaseFilter
{
    public function getFormControl()
    {
        return new NullControl($this->name);
    }
}