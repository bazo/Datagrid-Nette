<?php
namespace Tatami\Components\Datagrid\Forms\Controls;
use Nette\Utils\Html, Nette\Utils\Arrays, \Nette\Forms\Controls\TextInput;
/**
 * multipleDateField
 *
 * @author Martin Bažík
 * @package Core
 */
class multipleDateField extends TextInput
{

        /**
	 * Generates control's HTML element.
	 * @return Html
	 */
	public function getControl()
	{
            $fromControl = Html::el('input')->class('datepicker')->autocomplete('off');
            $fromControl->name = $this->getHtmlName('from');
            $fromControl->disabled = $this->disabled;
            $fromControl->id = $this->getHtmlId().'-from';
            if(isset($this->value['from'])) $fromControl->value($this->value['from']);

            $toControl = Html::el('input')->class('datepicker')->autocomplete('off');
            $toControl->name = $this->getHtmlName('to');
            $toControl->disabled = $this->disabled;
            $toControl->id = $this->getHtmlId().'-to';
            if(isset($this->value['to'])) $toControl->value($this->value['to']);
            

            $control = Html::el('span')->add($fromControl)->add($toControl);
            $control->disabled = $this->disabled;
            return $control;
	}

        /**
	 * Sets control's value.
	 * @param  string
	 * @return TextBase  provides a fluent interface
	 */
	public function setValue($value)
	{
            $this->value = \is_array($value) ? $value : array();//array('from' => null, 'to' => null);
            return $this;
	}

	/**
	 * Returns control's value.
	 * @return string
	 */
	public function getValue()
	{
            return $this->value;
	}

        /**
	 * Loads HTTP data.
	 * @return void
	 */
	public function loadHttpData()
	{
            $path = \explode('[', \strtr(\str_replace(array('[]', ']'), '', $this->getHtmlName()), '.', '_'));

            $origValue = Arrays::get($this->getForm()->getHttpData(), $path);
            $from = isset($origValue[0]) ? $origValue[0] : '';
            $to = isset($origValue[1]) ? $origValue[1] : '';
            $value = array(
                'from' => $from,
                'to' => $to
            );
            $this->setValue($value);
	}
}