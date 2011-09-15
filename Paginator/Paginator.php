<?php
namespace Tatami\Components\Datagrid;
use Nette\Application\UI\Control, \Nette\ComponentModel\IContainer;
/**
 * Paginator
 *
 * @author Martin Bažík
 */
class Paginator extends Control
{
    public
        /**
         * @var int
         */
        $totalCount,

        /**
         * @var int
         * @persistent
         */
        $itemsPerPage,

        /**
         * @var int
         * @persistent
         */
        $page = 1
    ;


    public $onPageChange = array();

    public function  __construct(IContainer $parent = NULL, $name = NULL)
    {
        parent::__construct($parent, $name);
    }

    public function handleChangePage($page)
    {
        $page = $page != null ? $page : 1;
        $this->page = $page;
        $this->onPageChange();
    }

    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }

    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    public function createComponentForm($name)
    {
        $form = new AppForm($this, $name);
        $form->getElementPrototype()->class('ajax');
        $form->addSubmit('btnSave', 'Save');
        $form['btnSave']->getControlPrototype()->title('Apply')->class = 'datagrid-button datagrid-button-apply';
        $options = $this->parent->getPaginatorOptions();
        $form->addSelect('items_per_page', 'Display per page', array_combine($options['displayedItems'], $options['displayedItems']))->setDefaultValue($this->itemsPerPage);
        $renderer = $form->getRenderer();
        $renderer->wrappers['pair']['container'] = null;
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['control']['container'] = null;
        $renderer->wrappers['label']['container'] = null;
        $form->onSubmit[] = callback($this, 'formSubmitted');
        return $form;
    }

    public function formSubmitted(AppForm $form)
    {
        $values = $form->getValues();
        $this->parent->itemsPerPage = $values['items_per_page'];
        $this->itemsPerPage = $values['items_per_page'];
        $this->parent->invalidateControl('table');
        $this->parent->invalidateControl('paginator');
    }

    /**
     * Renders paginator.
     * @return void
     */
    public function render()
    {
        $page = $this->page;
        $pageCount = (int)ceil($this->totalCount / $this->itemsPerPage);
        $firstPage = (int)1;
        $lastPage = $pageCount;
        $previousPage = $page - 1 > 0 ? $page - 1 : null;
        $nextPage = $page + 1 <= $lastPage ? $page + 1 : null;
        $from = ($page-1)*$this->itemsPerPage + 1;
        $to = $page*$this->itemsPerPage < $this->totalCount ? $page*$this->itemsPerPage : $this->totalCount;
        $this->template->setTranslator(Environment::getService('Nette\ITranslator'));
        $this->template->totalCount = $this->totalCount;
        $this->template->page = $page;
        $this->template->pageCount = $pageCount;
        $this->template->firstPage = $firstPage;
        $this->template->lastPage = $lastPage;
        $this->template->previousPage = $previousPage;
        $this->template->nextPage = $nextPage;
        $this->template->from = $from;
        $this->template->to = $to;
        $this->template->setFile(dirname(__FILE__) . '/template.phtml');
        $this->template->render();
    }
}