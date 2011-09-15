<?php
namespace Tatami\Components\Datagrid\Columns;
/**
 * BoolColumn
 *
 * @author Martin Bažík
 */
class BoolColumn extends BaseColumn
{
    protected
        $defaultFilterType = 'array'
    ;

    public function formatValue($value)
    {
	if((int)$value == 1 ) return __('yes');
	else return __('no');
    }

    public function getFilter()
    {
        return $this->getComponent('filter')->getFormControl()->setItems(
		array(
		    '' => '*',
		    '0' => __('no'),
		    '1' => __('yes')
		));
    }
}