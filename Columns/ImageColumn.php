<?php
namespace Tatami\Components\Datagrid\Columns;
use Nette\Utils\Html;
/**
 * Description of ImageColumn
 *
 * @author Martin
 */
class ImageColumn extends BaseColumn
{
    protected 
	$width = null,
	$height = null,
	$defaultFilterType = 'null'
    ;

    public function setWidth($width)
    {
	    $this->width = $width;
    }

    public function setHeight($height)
    {
	    $this->height = $height;
    }

    public function getWidth()
    {
	    return $this->width;
    }

    public function getHeight()
    {
	    return $this->height;
    }
    
    protected function  formatValue($value)
    { 
    	$style = array();
    	$style['width'] = "";
    	$style['height'] = "";
    	
    	if($this->width != null)
    	  $style['width'] = 'width:'.$this->width.'px;';
    	  
        if($this->height != null)
          $style['height'] = 'height:'.$this->height.'px;';

    	if($value != "" && $value != null)
          return Html::el('img', array('style'=>$style['width'].$style['height']))->src($value);
        else
          return Html::el('img')->src(Environment::getVariable('baseUri').'images/no-image.png');
    }

    protected function onCellRender()
    {
        foreach($this->onCellRender as $function)
        {
            $this->value = Html::el('img')->src($function($this->value, $this->record));
        }
    }
}