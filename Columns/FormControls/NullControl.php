<?php
namespace Tatami\Components\Datagrid\Forms\Controls;
use Nette\Forms\Controls\BaseControl;
/**
 * Description of NullControl
 *
 * @author Martin
 */
class NullControl extends BaseControl
{
    /**
     * @param  string  caption
     */
    public function __construct($caption = NULL)
    {
	    $this->monitor('Form');
	    parent::__construct();
	    $this->control = Html::el('span')->add(_('No filter'));
	    $this->label = Html::el('label');
    }
}