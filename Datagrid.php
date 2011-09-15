<?php

namespace Tatami\Components\Datagrid;

class DatagridException extends \Exception
{
    
}
/**
 * Description of Datagrid
 *
 * @author Martin
 */
class Datagrid extends \Nette\Application\UI\Control
{
    private
	/**
	 * stores the column names
	 */
        $columnNames = null,

	/**
	 * stores the actual columns that will be shown
	 */
        $columns = null,

	/**
	 * info about all columns
	 */
        $columnsInfo,

        /** @var DibiDataSource */
        $ds = null,
        
        /** @var array */
        $actionColumns = array(),

        /** @var int */
        $columnCount = 0,

        /** @var array */
        $records = array(),

        /** @var int */
        $totalCount,

        /** @var bool */
        $operationsEnabled = false,

        /** @var array */
        $operations = array(),

        $columnsAdded = false,
	$columnsPrepared = false,

        /** @var array */
        $paginatorOptions = array(
            'displayedItems' => array('2', '5', '10', '20', '30', '40', '50', '100', '200', '500', '1000'),
            'defaultItem' => '5',
            'itemsPerPage' => '5'
        ),

        /** @var string */
        $class,

        $enableConfig = false,

        $customHtml = array(),
        $inlineEditEnabled = false,
        $columnAliases = array()
    ;

    public

        /** @var bool */
        $hasColumns = false,

        /** @var bool */
        $hasFilters = false,

        /** @var bool */
        $autoDiscoverColumns = false,

        /** @var bool */
        $autoFilter = true,

        $onRowEdit = array()
    ;

    const
        MOVE_COLUMN_LEFT = 'left',
        MOVE_COLUMN_RIGHT = 'right',
        ORDER_BY_DESC = 'DESC',
        ORDER_BY_ASC = 'ASC'
    ;

    /**
     * Enables inline editing of columns
     * @return DibiDatagrid
     */
    public function enableInlineEdit()
    {
        $this->inlineEditEnabled = true;
        return $this;
    }

    /**
     * Disables inline editing of columns
     * @return DibiDatagrid
     */
    public function disableInlineEdit()
    {
        $this->inlineEditEnabled = false;
        return $this;
    }

    /**
     * Returns if inline editing is enabled
     * @return bool
     */
    public function getInlineEditEnabled()
    {
        return $this->inlineEditEnabled;
    }

    /**
     * Adds custom Html element to the grid
     * @param Html $html
     * @return DibiDatagrid
     */
    public function addCustomHtml(Html $html)
    {
        $this->customHtml[] = $html;
        return $this;
    }

    /**
     * Disables config
     * @return DibiDatagrid
     */
    public function disableConfig()
    {
        $this->enableConfig = false;
        return $this;
    }

    /**
     * Enables config
     * @return DibiDatagrid
     */
    public function enableConfig()
    {
        $this->enableConfig = true;
        return $this;
    }

    /**
     * Returns if config is enabled
     * @return bool
     */
    public function configEnabled()
    {
        return $this->enableConfig;
    }

    /**
     * Returns how many rows are being displayed per page
     * @return int
     */
    public function getItemsPerPage()
    {
        if(!isset($this->cache['itemsPerPage'])) $this->cache->save('itemsPerPage', $this->paginatorOptions['itemsPerPage']);
        return $this->cache['itemsPerPage'];
    }

    /**
     * Sets how many items per page will be displayed
     * @param int $value
     * @return DibiDatagrid 
     */
    public function setItemsPerPage($value)
    {
        $this->cache->save('itemsPerPage', $value);
        return $this;
    }

    /**
     * Returns filters
     * @return array of IDibiFilter
     */
    public function getFilters()
    {
        if(!isset($this->cache['filters'])) $this->cache->save('filters', array());
        return $this->cache['filters'];
    }

    /**
     * Sets filters
     * @param array of IDibiFilter $value
     * @return DibiDatagrid
     */
    public function setFilters($value)
    {
        $this->cache->save('filters', $value,  array(Cache::TAGS => array('filters')));
        return $this;
    }

    /**
     *
     * @return array
     */
    public function getOldRecords()
    {
        if(!isset($this->cache['oldRecords'])) $this->cache->save('oldRecords', array(), array(Cache::TAGS => array('oldRecords')));
        return $this->cache['oldRecords'];
    }

    public function setOldRecords($value)
    {
        $this->cache->save('oldRecords', $value,  array(Cache::TAGS => array('oldRecords')));
        return $this;
    }

    /**
     * Returns paginator options
     * @return array
     */
    public function getPaginatorOptions()
    {
        return $this->paginatorOptions;
    }

    /**
     * Sets the paginator options
     * @param array $paginatorOptions
     * @return DibiDatagrid
     */
    public function setPaginatorOptions($paginatorOptions)
    {
        $this->paginatorOptions = $paginatorOptions;
	return $this;
    }

    /**
     * returns Cache namespace
     * @return Cache
     */
    public function getCache()
    {
        return Environment::getCache(sha1($this->parent->name.$this->name. $this->presenter->getUser()->id));
    }

    /**
     * gets column
     * @return array
     */
    public function getColumnAliases()
    {
        return $this->columnAliases;
    }

    public function handleReset()
    {
        $this->cache->release();
        $this->cache->clean(array(Cache::TAGS => array('filters', 'oldRecords', 'selectedColumns', 'ordering', 'columnsInfo', 'itemsPerPage')));
        $this->cache->offsetUnset('filters');
        $this->cache->offsetUnset('oldRecords');
        $this->cache->offsetUnset('selectedColumns');
        $this->cache->offsetUnset('ordering');
        $this->cache->offsetUnset('columnsInfo');
        $this->cache->offsetUnset('itemsPerPage');
        $this->invalidateControl();
    }

    /**
     * @param string $columnName
     * @param string $columnType
     * @return BaseColumn
     */
    public function addColumn($columnName, $dataType = 'string', $options = null)
    {
	$this->columns[$columnName]['type'] = $dataType;
	$this->columnNames[$columnName] = $columnName;
	$this->columnCount++;
	//$this->cache->save('selectedColumns', $this->columnNames);
	$this->columnsAdded = true;
	return ColumnMapper::Map($columnName, $this, $dataType, $options);
    }
 

    private function getTotalCount()
    {
        try
	{
            $this->totalCount = $this->ds->count();
        }
        catch(DibiDriverException $e)
        {
            $this->flashMessage($e->getMessage(), 'error');
        }
    }

    public function setTotalCount($totalCount)
    {
        $this->totalCount = $totalCount;
    }

    /**
     * @param string $columnName
     * @return BaseColumn
     */
    public function getColumn($columnName)
    {
        return $this->getComponent($columnName);
    }
    
    /**
     * Fetches columns information
     * @return array
     */
    private function getColumnsInfo()
    {
	try
	{
	    if($this->cache['columnsInfo'] == null) 
	    {
		//columns info is saved in cache for later use
		$this->cache->save('columnsInfo', $this->ds->getResult()->getColumns(), array(Cache::TAGS => array('selectedColumns')));
	    }
	}
	catch(DibiDriverException $e)
	{
	    $this->flashMessage($e->getMessage());
	    $this->invalidateControl('flashes');
	}

	return $this->cache['columnsInfo'];
	$this->columnsInfo = $columnsInfo;
    }
    
    /**
     *
     * @param string $columnName
     * @return DibiDatagrid
     */
    public function hideColumn($columnName)
    {
        $columnName = String::lower($columnName);
        $this->removeComponent($this->getComponent($columnName));
        unset($this->columnNames[$columnName]);
        $this->columnCount--;
        return $this;
    }

    /**
     * Returns if the grid has columns
     * @return bool
     */
    public function hasColumns()
    {
        if($this->columnCount > 0) return true; else return false;
    }

    /**
     * Checks if the grid has column with the specified name
     * @param <type> $columnName
     * @return <type>
     */
    public function hasColumn($columnName)
    {
        return in_array($columnName, $this->columnNames) ? true : false;
    }

    /**
     * Returns if datagrid has actions
     * @return bool
     */
    public function hasActions()
    {
        if(count($this->actionColumns) > 0) return true; else return false;
    }

    /**
     * Adss an operation
     * @param string $name
     * @param Callback||Closuer $callback
     * @param bool $irreversible
     */
    public function addOperation($name, $callback, $irreversible = false)
    {
        if(!($callback instanceof  Callback or $callback instanceof Closure))
            trigger_error(sprintf('Argument 2 passed to %s must be an instance of Callback or Closure, %s given', __METHOD__, get_class($callback)));
        $this->operationsEnabled = true;
        $this->operations[$name]['callback'] = $callback;
        $this->operations[$name]['irreversible'] = $irreversible;
    }

    /**
     * Adds action column
     * @param string $title
     * @return DibiDatagrid_ActionColumn
     */
    public function addActionColumn($title = 'actions')
    {
        $this->actionColumns[] = $title;
        return new DibiDatagrid_ActionColumn($this, $title);
    }

    /**
     * Binds datasource
     * @param DibiDataSource $ds 
     */
    public function bindDataSource(DibiDataSource $ds)
    {
        $this->ds = $ds;
    }

    private function findColumnInfo($columnName, &$columnsInfo)
    {
        foreach($columnsInfo as $columnInfo)
        {
            if($columnInfo->getName() == $columnName) return $columnInfo;
        }
    }

    /**
     * Prepares columns names and their filters
     */
    protected function prepareColumnNames()
    {
	if($this->columnsPrepared != true)
	{
	    //gets the info about all columns in resultset
	    try
	    {
		if($this->cache['columnsInfo'] == null) 
		{
		    $this->cache->save('columnsInfo', $this->ds->getResult()->getColumns(), array(Cache::TAGS => array('selectedColumns')));
		}
	    }
	    catch(DibiDriverException $e)
	    {
		$this->flashMessage($e->getMessage());
		$this->invalidateControl('flashes');
	    }
	    //columns info is saved in cache for later use
	    $columnsInfo = $this->cache['columnsInfo'];
	    $this->columnsInfo = $columnsInfo;

	    $columns = array();
	    //setup columns which are selected and saved
	    if($this->cache['selectedColumns'] != null)
	    {
		foreach($this->cache['selectedColumns'] as $columnName => $checked)
		{
		    $columnInfo = $this->findColumnInfo($columnName, $columnsInfo);
		    if($columnInfo != null)
			$columns[$columnName]['type'] = ColumnMapper::MapDataType($columnInfo->getNativeType());
		}
		$this->columns = $columns;
		$this->columnNames = array_combine(array_keys($columns), array_keys($columns)) ;
	    }
	    //if no selected columns
	    elseif($this->cache['selectedColumns'] == null)
	    {
		foreach($columnsInfo as $columnInfo)
		{
		    $columns[$columnInfo->getName()]['type'] = ColumnMapper::MapDataType($columnInfo->getNativeType());
		}
		$this->columns = $columns;
		$this->columnNames = array_combine(array_keys($columns), array_keys($columns)) ;

	    }
	//save the info about which columns are selected
        if($this->cache['selectedColumns'] == null)
        {
            $selectedColumns = array_fill_keys(array_keys($this->columns), true);
            $this->cache->save('selectedColumns', $selectedColumns, array(Cache::TAGS => array('selectedColumns')));
        }
	$this->columnsPrepared = true;
	}
    }
    
    
    /**
     * Prepares columns names and their filters in mixed mode, showing only manually added columns at first, but leaving the option to edit the grid
     */
    protected function prepareColumnNamesMixedMode()
    {
	if($this->columnsPrepared != true)
	{
	    $this->columnsInfo = $this->getColumnsInfo();

	    $columns = array();
	    //setup columns which are selected and saved
	    if($this->cache['selectedColumns'] != null)
	    {
		foreach($this->cache['selectedColumns'] as $columnName => $checked)
		{
		    $columnInfo = $this->findColumnInfo($columnName, $this->columnsInfo);
		    if($columnInfo != null)
			$columns[$columnName]['type'] = ColumnMapper::MapDataType($columnInfo->getNativeType());
		}
		$this->columns = $columns;
		$this->columnNames = array_combine(array_keys($columns), array_keys($columns)) ;
	    }
	    //if no selected columns
	    elseif($this->cache['selectedColumns'] == null)
	    {
		$this->columnNames = array_combine(array_keys($this->columns), array_keys($this->columns)) ;
		$selectedColumns = array_fill_keys(array_keys($this->columns), true);
		$this->cache->save('selectedColumns', $selectedColumns, array(Cache::TAGS => array('selectedColumns')));
	    }
	    $this->columnsPrepared = true;
	}
    }

    /**
     * FINALLY ADD COLUMNS
     */
    protected function prepareColumns()
    {
	if($this->columns == 'null') throw new DatagridException ('There are no columns setup');
	foreach($this->columns as $field => $column)
	{
	    try
	    {
		ColumnMapper::Map($field, $this, $column['type']);
	    }
	    catch(InvalidStateException $e)
	    {
		//column already exist but we can add columns for non-existing fields in query
	    }
	    //now the column certainly exists as component
	    if($this->autoFilter === true)
	    {
		$column = $this->getColumn($field);
		if(!$column->hasFilter()) $column->setDefaultFilter(); //setFilter($column['type']);
	    }
	}
        $this->columnsAdded = true;
    }

    protected function applyFilters()
    {
        $this->filters = $this->cache['filters'] != null ? $this->cache['filters'] : array();
        foreach($this->filters as $filterObject)
        {
            $filterValue = $filterObject->getValue();
            if($filterObject->getValue() !== $filterObject->getEmptyValue() )
                $this->ds = $filterObject->apply($this->ds);
        }
        unset($this['form']); //TODO: inspect why is this here
    }

    protected function applyLimit()
    {
        $limit = $this->itemsPerPage;
        $page = $this['paginator']->getPage();
        $this->getTotalCount();
        $this['paginator']->setTotalCount($this->totalCount);
        $offset = ($page - 1)*$limit;
        $this->ds->applyLimit($limit, $offset);
    }

    protected function applyOrdering()
    {
        $ordering = $this->cache['ordering'];
        if($ordering == null) return;

        $orderBy = array();
        foreach($ordering as $ordering_array)
        {
            $orderBy = array_merge($orderBy, $ordering_array);
        }
        $this->ds->orderBy($orderBy);
    }

    protected function prepareData()
    {
        if($this->ds == null) throw new DatagridException(sprintf('DataSource not set.'));
        try
	{
            $this->records = $this->ds->fetchAll();
        }
        catch(DibiDriverException $e)
        {
            
        }
        $this->totalCount = count($this->records);
        if($this->records == null) $this->records = array();
        $this->oldRecords = $this->records;
    }

    private function fetchData()
    {
        if($this->columnsAdded == false && $this->autoDiscoverColumns == true)
	{
	    $this->prepareColumnNames();
	}
	elseif($this->columnsAdded == true && $this->autoDiscoverColumns == true)
	{
	    $this->prepareColumnNamesMixedMode();
	}
        $this->prepareColumns();
        $this->applyFilters();
        $this->applyLimit();
        $this->applyOrdering();
        $this->prepareData();
    }

    /** FILTERS */
    public function createComponentForm($name)
    {
	if($this->columnsAdded == false || $this->autoDiscoverColumns == true)
	{
	    $this->prepareColumnNames();
	}
	$this->prepareColumns();
        $form = new AppForm($this, $name);
        $form->getElementPrototype()->class('ajax');

	if($this->operationsEnabled and !empty($this->operations))
        {
            $operations = array_combine( array_keys($this->operations), array_keys($this->operations));
            $boxes = $form->addContainer('boxes');
            foreach($this->oldRecords as $key => $record)
            {
                $boxes->addCheckbox('row_'.$key)->getControlPrototype()->checked(false);
            }
            $form->addSelect('operation', 'Operation', $operations)->getControlPrototype()->class = 'operations';
            $form->addSubmit('btnExecuteOperation', 'Execute')->onClick[] = callback($this, 'executeOperationCallback');
            $form['btnExecuteOperation']->getControlPrototype()->setName('button')->class('btnExecute datagrid-button')->add(Html::el('span')->class('datagrid-icon datagrid-icon-apply'))
                    ->add(Html::el('span')->class('caption')->setText('Execute'));
        }

	//fix when submitting form, filters information gets setup later
	if($form->isSubmitted() == false)
	{
	    $this->cache->save('hasFilters', $this->hasFilters);
	}
	else
	{
	    $this->hasFilters = $this->cache['hasFilters'];
	}

        if($this->hasFilters)
        {
            $filters = $form->addContainer('filters');
            foreach($this->getComponents(false, 'IDatagridColumn') as $column)
            {
                if($column->hasFilter())
                {
                    $filters->addComponent($column->getFilter(), $column->name);
                    if (isset($this->filters[$column->name]))
                    {
                        $filters[$column->name]->setDefaultValue($this->filters[$column->name]->getValue());
                    }
                }
            }

            $form->addSubmit('btnApplyFilters', 'Apply filters')->onClick[] = callback($this, 'saveFilters');
            $form['btnApplyFilters']->getControlPrototype()->title('Apply filters')->class = 'datagrid-button datagrid-button-apply';
            $form->addSubmit('btnCancelFilters', 'Cancel filters')->onClick[] = callback($this, 'cancelFilters');
            $form['btnCancelFilters']->getControlPrototype()->title('Cancel filters')->class = 'datagrid-button datagrid-button-cancel';
        }
    }

    public function saveFilters(Button $button)
    {
        $form = $button->getForm();
        $values = $form->getValues();
        $filters = $values['filters'];
        $filterObjects = $this->filters;
        foreach($filters as $filter => $value)
        {
            $filterObjects[$filter] = $this->getComponent($filter)->getComponent('filter')->getFilter($value);//apply($this->ds, $value);
        }
        $this->filters = $filterObjects;
        $this->invalidateControl('table');
        $this->invalidateControl('paginator');
    }

    public function cancelFilters(Button $button)
    {
        $this->filters = array();
        $this->invalidateControl('table');
        $this->invalidateControl('paginator');
    }

    public function executeOperationCallback(Button $button)
    {
        $form = $button->getForm();
        $values = $form->getValues();
        $operation = $values['operation'];
        $boxes = $values['boxes'];
        $records = array();
        $oldRecords = $this->cache['oldRecords'];
        foreach($boxes as $key => $box)
        {
            if($box == true)
            {
                $row_id = (int)str_replace('row_', '', $key);
                $records[] = $oldRecords[$row_id];
            }
        }

        if(count($records) == 0)
        {
            $this->flashMessage(sprintf('No records selected'), 'warning');
            $this->invalidateControl('flashes');
        }
        else
        {
            if($this->operations[$operation]['irreversible'] == true)
            {
                $token = sha1($this->name.$operation.(string)time().Environment::getUser()->id);
                $yesLink = Html::el('a')->setText('Yes')->href($this->link('executeIrreversibleOperation!', array('token' => $token, 'execute' => true)))->class('ajax');
                $noLink = Html::el('a')->setText('No')->href($this->link('executeIrreversibleOperation!', array('token' => $token, 'execute' => false)))->class('ajax');
                $this->flashMessage(sprintf('Operation %s is irreversible. Really continue? %s %s', $operation, $yesLink, $noLink), 'warning permanent');
                $session = Environment::getSession($token);
                $session->operation = $operation;
                $session->records = $records;
            }
            else $message = $this->operations[$operation]['callback']($records, $operation);

            if(@$message instanceof stdClass)
                $this->flashMessage($message->message, $message->type);
            elseif(is_array($message))
                $this->flashMessage ($message['message'], $message['type']);
            elseif(@is_string($message))
                $this->flashMessage($message);
        
            $this->invalidateControl('flashes');
            $this->invalidateControl('table');
            $this->invalidateControl('paginator');
        }
    }

    public function handleExecuteIrreversibleOperation($token, $execute)
    {
        $execute = (bool)$execute;
        $session = Environment::getSession();
        if($session->hasNamespace($token))
        {
            $ns = $session->getNamespace($token);
	    $operation = $ns->operation;
            if($execute === false)
            {
                $ns->remove();
                $this->flashMessage(sprintf('Operation %s canceled!', $operation));
            }
            elseif($execute === true)
            {
                $records = $ns->records;
                $operation = $ns->operation;
                $ns->remove(); //delete session namespace before executing operation in case something fails in execution
                $message = $this->operations[$operation]['callback']($records);

                if(@$message instanceof stdClass)
                    $this->flashMessage($message->message, $message->type);
                elseif(@is_string($message))
                    $this->flashMessage($message);

                $this->invalidateControl('paginator');
            }
        }
        else $this->flashMessage(sprintf('Invalid operation') ,'error');
        $this->invalidateControl('flashes');
        $this->invalidateControl('table');
    }

    /* PAGINATION */

    public function createComponentPaginator($name)
    {
        $p = new DibiDatagridPaginator($this, $name);
        $p->setItemsPerPage($this->itemsPerPage);
        $this->getTotalCount();
        $p->setTotalCount($this->totalCount);
        $p->onPageChange[] = callback($this, 'pageChanged');
        return $p;
    }

    public function pageChanged()
    {
        $this->invalidateControl('table');
        $this->invalidateControl('paginator');
    }

    public function handleShowConfig()
    {
        $this->showConfig = true;
        $this->invalidateControl('config');
    }

    public function createComponentConfig($name)
    {
        $form = new AppForm($this, $name);
        $form->getElementPrototype()->class = 'ajax';
        $columns = array();
        if($this->autoDiscoverColumns == true)
        {
            $columnsInfo = $this->cache['columnsInfo'] != null ? $this->cache['columnsInfo'] : array();
            foreach($columnsInfo as $column)
            {
                try{
                    $columns[$column->getTable()->getName()][$column->getName()] = $column->getName();
                }
                catch(DibiException $e)
                {
                    $columns['unknown'][$column->getName()] = $column->getName();
                }
            }

            foreach($columns as $table => $columnNames)
            {
                $form->addGroup($table);
                foreach($columnNames as $columnName)
                {
                    $form->addCheckbox($columnName, $columnName);
                }
            }
        }
        //unset($columns);

        $renderer = $form->getRenderer();
        $renderer->wrappers['pair']['container'] = Html::el('span')->class('pair');
        $renderer->wrappers['controls']['container'] = null;
        $renderer->wrappers['control']['container'] = null;
        $renderer->wrappers['label']['container'] = null;
        $form->addGroup();
        $form->addSubmit('btnApply', 'Save', 'apply');
        $form['btnApply']->getControlPrototype()->setName('button')->class('button btnApply')->add(Html::el('span')->class('datagrid-icon datagrid-icon-apply'))
                    ->add(Html::el('span')->class('caption')->setText('Save'));
        $form->onSubmit[] = callback($this, 'saveColumns');

        $form->setDefaults((array)$this->cache['selectedColumns']);
        return $form;
    }

    public function saveColumns(Form $form)
    {
        $values = $form->getValues();
        foreach($values as $column => $checked)
        {
           if($checked) $selectedColumns[$column] = true;
        }
        $this->cache->save('selectedColumns', $selectedColumns, array(Cache::TAGS => array('selectedColumns')));
        $this->flashMessage(sprintf('Columns setup successful'), 'ok');
        $this->invalidateControl('table');
        $this->invalidateControl('flashes');
    }

    public function handleRemoveColumn($column)
    {
        $selectedColumns = $this->cache['selectedColumns'];
        unset($selectedColumns[$column]);
        $this->cache->save('selectedColumns', $selectedColumns, array(Cache::TAGS => array('selectedColumns')));
        $this->invalidateControl('table');
        $this->invalidateControl('configurator');
    }

    public function handleMoveColumn($column, $direction)
    {
        $selectedColumns = $this->cache['selectedColumns'];

        $keys = array_keys($selectedColumns);
        $currentColumnIndex = array_search($column, $keys);

        switch($direction)
        {
            case self::MOVE_COLUMN_LEFT:
                $otherColumnIndex = $currentColumnIndex - 1;
            break;

            case self::MOVE_COLUMN_RIGHT:
                $otherColumnIndex = $currentColumnIndex + 1;
            break;
        }

        if($otherColumnIndex < 0) $otherColumnIndex = 0;
        If($otherColumnIndex >= count($keys)) $otherColumnIndex = count($keys) - 1;

        if($currentColumnIndex != $otherColumnIndex)
        {
            $otherColumn = $keys[$otherColumnIndex];

            $keys[$otherColumnIndex] = $column;
            $keys[$currentColumnIndex] = $otherColumn;
            $selectedColumns = array_fill_keys($keys, true);

            $orderBy = $this->cache['ordering'];
            unset($orderBy[$currentColumnIndex]);
            $this->cache->save('ordering', $orderBy, array(Cache::TAGS => array('ordering')));

            $this->cache->save('selectedColumns', $selectedColumns, array(Cache::TAGS => array('selectedColumns')));
            $this->invalidateControl('table');
        }
    }

    public function handleOrderByColumn($column)
    {
        $ordering = $this->cache['ordering'];
        $selectedColumns = $this->cache['selectedColumns'];
        
        $keys = array_keys($selectedColumns);
        $key = array_search($column, $keys);
        if(isset($ordering[$key][$column]))
        {
            $currentDirection = $ordering[$key][$column];
            switch($currentDirection)
            {
                case self::ORDER_BY_DESC:
                    $direction = self::ORDER_BY_ASC;
                    $ordering[$key] = array($column => $direction);
                break;

                case self::ORDER_BY_ASC:
                    unset($ordering[$key]);
                break;
            }
        }
        else
        {
            $direction = self::ORDER_BY_DESC;
            $ordering[$key] = array($column => $direction);
        }

        $this->cache->save('ordering', $ordering, array(Cache::TAGS => array('ordering')));
        $this->invalidateControl('table');
        $this->invalidateControl('paginator');
    }

    public function render()
    {
	$this->template->setFile(__DIR__ . '/template.latte');
	
        if(empty($this->records)) $this->fetchData();
        $this->template->setTranslator(Environment::getService('Nette\ITranslator'));
        $this->template->operationsEnabled = $this->operationsEnabled;
        $this->template->columnsInfo = $this->columnsInfo;
        $this->template->totalCount = $this->totalCount;
        $this->template->columnNames = $this->columnNames;
        $this->template->columns = $this->columns;
        $this->template->records = $this->records;
        $this->template->actionColumns = $this->actionColumns;
        $this->template->ordering = $this->cache['ordering'];
        $this->template->gridClass = $this->class;
        $this->template->enableConfig = $this->enableConfig;
        $this->template->customHtml = $this->customHtml;
        try
        {
            $this->template->render();
        }
        catch (Exception $e)
        {
            echo $e->getMessage();
        }
    }
}
