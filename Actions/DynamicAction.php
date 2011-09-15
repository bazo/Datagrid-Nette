<?php
namespace Tatami\Components\Datagrid\Actions;
use Nette\ComponentModel\Component,    Nette\Utils\Html,    Nette\Utils\Strings;
/**
 * Description of DynamicActionColumn
 *
 * @author Martin
 */
class DynamicAction extends Action
{
    public
        $dynamicChange
    ;

    public function render()
    {
        $this->fillParams();
        $output = '';
        $this->dynamicChange[0]($this->value, $this->record, $this);
        if($this->showTitle == true) $title = $this->title; else $title = '';
        if(empty($this->onActionRender))
        {
            $presenter = Environment::getApplication()->getPresenter();
            $icon = $this->icon != null ? $this->icon : $this->title;
            $output = Html::el('a')->add(Html::el('span')->class(sprintf('icon icon-%s', Strings::lower($icon))))
                       ->href($presenter->link($this->destination, array($this->key => $this->value) + $this->params))->title($this->title)
                       ->add($title)
                      ;//->setText($title);
        }
        else
        {
            foreach ($this->onActionRender as $function)
            {
                $output .= $function($this->value, $this->record, $this);
            }
        }
        if($this->ajax) $output->addClass('ajax');
        return $output;
    }
}