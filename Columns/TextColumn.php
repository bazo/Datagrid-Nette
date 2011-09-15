<?php
namespace Tatami\Components\Datagrid\Columns;
use Nette\Utils\Strings, \Nette\Utils\Html;
/**
 * Description of TextColumn
 *
 * @author Martin
 */
class TextColumn extends BaseColumn
{
    protected function  formatValue($value)
    {
        if( Strings::length($value) > 30 )
        {
            $newValue =  Strings::truncate($value, 30, '...');
            return Html::el('span')->setText($newValue)->title($value);
        }
        return $value;
    }
}