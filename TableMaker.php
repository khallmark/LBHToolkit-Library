<?php
/**
 * TableMaker.php
 * LBHToolkit_TableMaker
 * 
 * The TableMaker is used to generate an HTML table from a set of array accessible
 * data. It is a Zend_Controller_Action_Helper that works in conjunction with the
 * Zend request object in order to drive its parameters.
 * 
 * LICENSE
 * 
 * This file is subject to the New BSD License that is bundled with this package.
 * It is available in the LICENSE file. 
 * 
 * It is also available online at http://www.littleblackhat.com/lbhtoolkit
 * 
 * @author		Kevin Hallmark <kevin.hallmark@littleblackhat.com>
 * @since		2011-08-24
 * @package		LBHToolkit
 * @subpackage	TableMaker
 * @copyright	Little Black Hat, 2011
 * @license		http://www.littleblackhat.com/lbhtoolkit	New BSD License
 */

class LBHToolkit_TableMaker extends Zend_Controller_Action_Helper_Abstract implements LBHToolkit_TableMaker_Interface
{
	/**
	 * Standard Parameters
	 *
	 * @var array
	 */
	protected $_params = array();

	/**
	 * The defaut page size for a tablemaker result set
	 */
	const PAGE_SIZE = 20;
	const PAGE_SIZE_MAX = 100;
	
	/**
	 * Added to make changing the default decorator type easier
	 */
	const DEFAULT_DECORATOR_TYPE = 'body';
	
	/**
	 * The columns for the table
	 * 
	 * @var array
	 */
	protected $_columns;
	
	/**
	 * The adapter used to load the data
	 *
	 * @var LBHToolkit_TableMaker_Adapter_Interface
	 */
	protected $_adapter = NULL;
	
	/**
	 * The types of decorators supported by this object
	 * 
	 * output: decorator for the whole table body
	 * header: decorator for the header row
	 * body:   decorator for the body rows
	 *
	 * @var string
	 */
	protected $_decorator_types = array('output', 'header', 'body');


	protected $_decorators = array();

	protected $_template_vars = array();
	
	protected $_searchable_fields = array();
	
	/**
	 * Temporarily stores the paging information
	 *
	 * @var string
	 */
	protected $_paging_info = NULL;
	
	/**
	 * Allows this plugin to be instantiated more easily from the HelperBroker
	 *
	 * @param string $params 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function direct($params)
	{
		$this->setDefaultParams();
		
		if ($params !== NULL)
		{
			$this->setParams($params);
		}
		
		$this->init();
		
		$this->validateParams($params);
		
		$this->initData();
		
		$this->render_started = FALSE;
		
		return $this;
	}
	
	public function __construct($params = NULL)
	{
		if ($params !== NULL)
		{
			$this->setDefaultParams();
			
			if ($params !== NULL)
			{
				$this->setParams($params);
			}
			
			$this->init();
			
			$this->validateParams($params);
			
			$this->initData();
			
			$this->render_started = FALSE;
		}
	}
	
	
	/**
	 * Called at the end of the constructor to allow custom subclass behavior
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function init()
	{
		
	}
	
	
	/**
	 * Called at the end of the constructor to allow custom subclass behavior
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function initData()
	{
		
	}
	
	/**
	 * Setup and validate the parameters
	 *
	 * @param string $params 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function validateParams($params)
	{
		$this->data_count = 0;
		
		if (!$this->adapter)
		{
			throw new LBHToolkit_TableMaker_Exception("No Adapter Specified");
		}
		
		if (!is_object($this->_adapter))
		{
			$this->_adapter = new $this->adapter;
		}
		else
		{
			$this->_adapter = $this->adapter;
			$this->adapter = NULL;
		}
		
		$this->getAdapter()->primary_key = $this->id;
		
		if (!$this->count)
		{
			$this->count = LBHToolkit_TableMaker::PAGE_SIZE;
		}
		
		if ($this->pre_load)
		{
			if (!is_callable($this->pre_load))
			{
				throw new LBHToolkit_TableMaker_Exception("pre_load is not callable");
			}
		}
		
		if ($this->post_load)
		{
			if (!is_callable($this->post_load))
			{
				throw new LBHToolkit_TableMaker_Exception("post_load is not callable");
			}
		}

		
		// If header decorators were passed, run them through the decorator add function
		if ($decorators = $this->output_decorators)
		{
			$decorators = array_reverse($decorators);
			
			foreach ($decorators AS $name => $decorator)
			{
				$this->addDecorator($name, $decorator, 'output');
			}
		}
		
		// If header decorators were passed, run them through the decorator add function
		if ($decorators = $this->header_decorators)
		{
			$decorators = array_reverse($decorators);
			
			foreach ($decorators AS $name => $decorator)
			{
				$this->addDecorator($name, $decorator, 'header');
			}
		}
				
		// If decorators were passed, run them through the decorator add function
		if ($decorators = $this->body_decorators)
		{
			$decorators = array_reverse($decorators);
			
			foreach ($decorators AS $name => $decorator)
			{
				$this->addDecorator($name, $decorator, 'body');
			}
		}

		// Add the class, this is a deprecated setup option
		if ($this->class)
		{
			if ($decorator = $this->getDecorator('table', 'output'))
			{
				$decorator->addAttribute('class', $this->class);
			}
		}
		
	}
	
	public function setDefaultParams()
	{
		$this->id = 'id';
		$this->table_name = 'tablemaker';
		
		$this->show_header = TRUE;
		$this->show_pagination = TRUE;
		
		$this->use_modules = TRUE;

		$decorators = array(
			'output' => array(
				'div' => new LBHToolkit_TableMaker_Decorator_Tag(
					array(
						'tag' => 'div',
						'attributes' => array(
							'class' => 'tablemaker'
						)
					)
				),
				'table' => new LBHToolkit_TableMaker_Decorator_Tag_Table(
					array(
						'attributes' => array(
							'id' => '%%table_name%%'
						)
					)
				)
			),
			'header' => array(
				'thead' => new LBHToolkit_TableMaker_Decorator_Tag_Table_Head(
					array(
						'attributes' => array(
							'id' => '%%table_name%%-thead'
						)
					)
				),
				'tr' => new LBHToolkit_TableMaker_Decorator_Tag_Table_Row(
					array(
						'attributes' => array(
							'id' => '%%table_name%%-header'
						)
					)
				),
			),
			'body' => array(
				'tbody' => new LBHToolkit_TableMaker_Decorator_Tag_Table_Body(
					array(
						'attributes' => array(
							'id' => '%%table_name%%-tbody'
						)
					)
				),
				'tr' => new LBHToolkit_TableMaker_Decorator_Tag_Table_Row(
					array(
						'attributes' => array(
							'id' => '%%table_name%%-row-%%id%%',
						)
					)
				),
			)
		);
		
		foreach ($decorators AS $type => $type_decorators)
		{
			$this->addDecorators($type_decorators, $type);
		}

	}
	
	/**
	 * Adds a new column to the table
	 * 
	 * @param $column The column object or an initialization array
	 */
	public function hasColumn($column)
	{
		if (is_object($column))
		{
			if (!is_a($column, 'LBHToolkit_TableMaker_Column'))
			{
				throw new Memberfuse_Rest_Exception("The object you added is not a valid column object");
			}
		}
		else
		{
			$column = new LBHToolkit_TableMaker_Column($column);
			
			if ($this->getActionController())
			{
				$column->view = $this->getActionController()->view;
			}
		}

		$column->setTableMaker($this);
		
		$this->_columns[$column->column_id] = $column;
		
		if ($column->isSearchable())
		{
			$this->_searchable_fields[] = $column;
		}
		
		return $column;
	}
	
	/**
	 * Returns the column named $alias
	 *
	 * @param string $alias 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getColumn($alias)
	{
		if (isset($this->_columns[$alias]))
		{
			return $this->_columns[$alias];
		}
		
		return NULL;
	}
	
	public function getColumns()
	{
		return $this->_columns;
	}
	
	/**
	 * The Template Variables are passed on rendering to an partials rendered by
	 * the TableMaker.
	 *
	 * @param string $key 
	 * @param string $value 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function addTemplateVar($key, $value)
	{
		$this->_template_vars[$key] = $value;
	}
	
	/**
	 * This handles setting the paging info object
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getPagingInfo()
	{
		if (!$this->_paging_info)
		{
			$paging = array();
			
			$default_sort = NULL;
			if($this->default_sort)
			{
				$default_sort = $this->default_sort;
			}
			
			$default_order = NULL;
			if ($this->default_order)
			{
				$default_order = $this->default_order;
			}
			
			$paging['default_sort'] = $default_sort;
			$paging['default_order'] = $default_order;
			
			$paging['page'] = $this->getRequest()->getParam('page', 1);
			$paging['count'] = $this->getRequest()->getParam('count', $this->count);
			
			$paging['sort'] = $this->getRequest()->getParam('sort', $default_sort);
			$paging['order'] = $this->getRequest()->getParam('order', $default_order);
			
			$paging['sort_order'] = sprintf('%s.%s', $paging['sort'], $paging['order']);
			
			$paging['action'] = $this->getActionName();
			
			$paging['query'] = $this->getRequest()->getQuery();
			
			$pagingInfo = new LBHToolkit_TableMaker_Paging($paging);
			
			$this->setPagingInfo($pagingInfo);
		}
		
		
		return $this->_paging_info;
	}
	
	public function setPagingInfo(LBHToolkit_TableMaker_Paging $pagingInfo)
	{
		$this->_paging_info = $pagingInfo;
	}
	
	/**
	 * Get's the full action path including module/controller/action
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getActionName()
	{
		$moduleName = '';
		if ($this->use_modules)
		{
			$moduleName = '/' . $this->getRequest()->getModuleName();
			
		}
		$controllerName = '/' . $this->getRequest()->getControllerName();
		$actionName = '/' . $this->getRequest()->getActionName();
		
		$params = $this->getRequest()->getParams();
		
		$paramString = '';
		
		$ignored_params = array('module', 'controller', 'action', 'sort', 'order', 'page');
		foreach ($params AS $key => $value)
		{
			if (!in_array($key, $ignored_params))
			{
				$paramString .= '/' . $key . '/' . $value;
			}
		}
		
		$path = $this->getRequest()->getBaseUrl() . $moduleName . $controllerName . $actionName . $paramString;
		
		return $path;
	}

	/**
	 * Sets the raw data to the adapter. This would be a query in a DB based
	 * adapter or the entire array of data in an array preparation.
	 *
	 * @param string $data 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function setData($data)
	{
		$this->getAdapter()->setData($data);
	}
	
	/**
	 * Returns the data from the adapter including pre- and post- load hooks.
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getData()
	{
		// Get the paging info, needed for hook calls
		$pagingInfo = $this->getPagingInfo();
		
		// We need to get the total count before we attempt to load data
		$total_count = $this->getAdapter()->getTotalCount();
		
		// Run the preload hook
		$this->_preLoad($this, $pagingInfo);
		
		// Actually get the data from the tablemaker.
		$data = $this->getAdapter()->getData($pagingInfo);
		
		// Run the postload hook
		$this->_postLoad($this, $pagingInfo, $data);
		
		// Set the total count to the tablemaker
		$pagingInfo->setTotalCount($total_count);
		$pagingInfo->column_count = count($this->_columns);
		
		// Save the pagingInfo to the 
		$this->setPagingInfo($pagingInfo);
		
		return $data;
	}
	
	public function renderTable()
	{
		// This keeps you from calling functions out-of-turn
		$this->render_started = TRUE;
		
		// Get the query string
		$query = $this->getRequest()->getQuery();
		
		// Process the search form if it exists
		if (count($this->_searchable_fields))
		{
			foreach ($this->_searchable_fields AS $column)
			{
				if (isset($query[$column->column_id]))
				{
					$value = $query[$column->column_id];

					$this->getAdapter()->addFilter($column->search_query, $query[$column->column_id]);
				}
			}
		}

		// Get the data, updated pagingInfo and count
		$data = $this->getData();
		$pagingInfo = $this->getPagingInfo();
		$total_count = $this->getAdapter()->getTotalCount();
		
		if (count($data) == 0 || $total_count == 0)
		{
			return $this->renderEmpty();
		}
		
		// Mark the pagingation to show/hide
		$pagingInfo->show_pagination = $this->show_pagination;
		
		// Render the body
		$html = $this->renderHeader($data, $pagingInfo) . $this->render($data, $pagingInfo) . $pagingInfo->render($data, $pagingInfo);
		
		// And process our decorators
		$html = $this->_processDecorators(
			$html, 
			'output', 
			array(
				'tablemaker' => $this
			)
		);
		
		
		return $html;
	}
	
	protected function _preLoad($tablemaker, $pagingInfo)
	{
		if ($this->pre_load)
		{
			$pre_load = $this->pre_load;
			$pre_load($tablemaker, $pagingInfo);
		}
	}
	
	protected function _postLoad($tablemaker, $pagingInfo, &$data)
	{
		if ($this->post_load)
		{
			$post_load = $this->post_load;
			$post_load($tablemaker, $pagingInfo, $data);
		}
	}
	
	public function renderForm()
	{
		if (count($this->_columns) == 0)
		{
			return NULL;
		}
		
		$query = $this->getRequest()->getQuery();
		
		$form = new LBHToolkit_TableMaker_Search_Form();
		
		$form->setAction($this->getActionName());
		
		$columns = $this->_columns;
		foreach ($columns AS $column)
		{
			$value = NULL;
			
			if (isset($query[$column->column_id]))
			{
				$value = $query[$name];
			}
			
			$column->processSearchField($form, $value);
		}
		
		$form->addSubmit();
		
		return sprintf('<div class="tablemaker-search-form"><h3 class="tablemaker-search-title">Search %s</h3>%s</div>', $this->label, $form->render());
	}
	
	public function renderEmpty()
	{
		if($this->empty_text)
		{
			return sprintf("<p>%s</p>", $this->empty_text);
		}
		
		return "<p>No results were returned for your request.</p>";
	}
	
	public function renderHeader(&$data, LBHToolkit_TableMaker_Paging $pagingInfo)
	{
		if (!$this->render_started)
		{
			throw new LBHToolkit_TableMaker_Exception("I'm sorry Dave, I'm afraid I can't do that.. Please call renderTable().");
		}
		
		if (!$this->show_header)
		{
			return;
		}
		
		$columns = $this->_columns;
		
		if(count($columns) == 0)
		{
			throw new LBHToolkit_TableMaker_Exception("No columns provided for the table.");
		}
		
		$html = '';
		foreach ($columns AS $column)
		{
			$column->id = $this->id;
			$html = $html . $column->renderHeader($data, $pagingInfo);
		}
		
		$html = $this->_processDecorators($html, 'header', array('tablemaker' => $this));
		
		return $html;
	}
	
	/**
	 * Generates a 'table' element with the data in the object
	 *
	 * @access public
	 * 
	 */
	public function render(&$data, LBHToolkit_TableMaker_Paging $pagingInfo)
	{
		if (!$this->render_started)
		{
			throw new LBHToolkit_TableMaker_Exception("I'm sorry Dave, I'm afraid I can't do that. Please call renderTable().");
		}
		
		$columns = $this->getColumns();
		
		$html = $this->_preRenderDecorators('', array('tablemaker' => $this, 'html' => ''));
		
		foreach ($data AS $row)
		{
			$html .= $this->renderRow($row, $pagingInfo, $columns);
		}
		
		$html = $this->_postRenderDecorators($html, array('tablemaker' => $this, 'html' => $html));
		
		return $html;
	}
	
	/**
	 * Renders a SINGLE row. $pagingInfo and $columns should NOT be passed if you're 
	 * calling this function directly. Those values are ONLY passed by render()
	 *
	 * @param string $row 
	 * @param LBHToolkit_TableMaker_Paging $pagingInfo 
	 * @param string $columns 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function renderRow($row, LBHToolkit_TableMaker_Paging $pagingInfo = NULL, &$columns = NULL)
	{
		if (is_null($pagingInfo))
		{
			$pagingInfo = $this->getPagingInfo();
		}
		
		if (is_null($columns))
		{
			$columns = $this->getColumns();
		}
		
		$id = $this->getAdapter()->getPrimaryKey($row);//_dataValue($row, $this->id);
		
		$html = '';
		
		// Default arguments passed to function/view_helper/template
		$arguments = array(
			'row' => $row, 
			'id' => $id, 
			'tablemaker' => $this,
		);
		
		$row_html = '';
		foreach ($columns AS $column)
		{
			$arguments['row_value'] = $arguments['html'] = $this->_dataValue($row, $column->column_id, '');
			
			$row_html = $row_html . $column->render($row, $pagingInfo, $arguments);
			
			$arguments['html'] = $row_html;
		}
		
		$html .= $this->_processDecorators($row_html, 'body', $arguments);
		
		return $html;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Adds a decorator to the LBHToolkit_TableMaker_Abstract object
	 * 
	 * This is used in a TableMaker itself for rows
	 * This is used in a Column for rendering the column
	 * This is used in Paging for rendering the paging info at the bottom
	 *
	 * @param string $alias 
	 * @param LBHToolkit_TableMaker_Decorator_Interface $decorator 
	 * @param string $type
	 * @return LBHToolkit_TableMaker_Decorator_Interface
	 * @author Kevin Hallmark
	 */
	public function addDecorator($alias, LBHToolkit_TableMaker_Decorator_Interface $decorator, $type = self::DEFAULT_DECORATOR_TYPE)
	{
		$decorator->identifier = $alias;
		
		$decorators = $this->_getDecoratorReference($type);
		
		$decorators[$alias] = $decorator;
		
		$this->_decorators[$type] = $decorators;
		
		return $decorator;
	}
	
	/**
	 * Add an array of decorators to the object of type $type. Defaults to body
	 *
	 * @param string $decorators 
	 * @param string $type 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function addDecorators($decorators, $type = self::DEFAULT_DECORATOR_TYPE)
	{
		foreach ($decorators AS $alias => $decorator)
		{
			$this->addDecorator($alias, $decorator, $type);
		}
		
		return $decorators;
	}
	
	public function removeDecorator($alias, $type = self::DEFAULT_DECORATOR_TYPE)
	{
		$decorators = $this->_getDecoratorReference($type);
		
		if (isset($decorators[$alias]))
		{
			unset($decorators[$alias]);
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	public function getDecorator($alias, $type = self::DEFAULT_DECORATOR_TYPE)
	{
		$decorators = $this->_getDecoratorReference($type);
		
		if (isset($decorators[$alias]))
		{
			return $decorators[$alias];
		}
		
		return NULL;
	}
	
	/**
	 * Returns the decorators of $type
	 *
	 * @param string $type 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getDecorators($type = self::DEFAULT_DECORATOR_TYPE)
	{
		$this->_checkType($type);
		
		return $this->_decorators[$type];
	}
	
	public function setDecorators($decorators, $type = self::DEFAULT_DECORATOR_TYPE)
	{
		$this->_checkType($type);
		
		unset($this->_decorators[$type]);
		
		$this->addDecorators($decorators, $type);
	}

	protected function _preRenderDecorators($html, $arguments = array())
	{
		$decorators = $this->getDecorators('body');
		
		if (count($decorators))
		{
			$decorators = array_reverse($decorators);
			foreach ($decorators AS $decorator)
			{
				if ($view = $this->view)
				{
					$decorator->view = $view;
				}

				$html = $decorator->preRender($html, $arguments);
				
				$arguments['html'] = $html;
			}
		}
		
		return $html;
	}
	
	protected function _postRenderDecorators($html, $arguments = array())
	{
		$decorators = $this->getDecorators('body');
		
		if (count($decorators))
		{
			$decorators = array_reverse($decorators);
			foreach ($decorators AS $decorator)
			{
				if ($view = $this->view)
				{
					$decorator->view = $view;
				}
				
				$html = $decorator->postRender($html, $arguments);
			}
		}
		
		return $html;
	}
		
	protected function _processDecorators($html, $type = self::DEFAULT_DECORATOR_TYPE, $arguments = array())
	{
		$decorators = $this->getDecorators($type);
		
		if (count($decorators))
		{
			$decorators = array_reverse($decorators);
			foreach ($decorators AS $decorator)
			{
				if ($view = $this->view)
				{
					$decorator->view = $view;
				}
				$html = $decorator->format($html, $arguments);
				
				$arguments['html'] = $html;
			}
		}
		
		return $html;
	}
	
	
	
	/**
	 * Checks the type and returns a reference to the decorator array
	 *
	 * @param string $type 
	 * @return array
	 * @author Kevin Hallmark
	 */
	protected function &_getDecoratorReference($type)
	{
		$this->_checkType($type);
		
		if (!isset($this->_decorators[$type]))
		{
			$this->_decorators[$type] = array();
		}
		
		return $this->_decorators[$type];
	}
	
	protected function _checkType($type)
	{
		if (!in_array($type, $this->_decorator_types))
		{
			$message = sprintf(
				'Decorator type %s not supported. Valid values: %s',
				$type,
				implode(', ', $this->_decorator_types)
			);
			
			throw new LBHToolkit_TableMaker_Exception($message);
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Get from the params array. Used for serialization
	 *
	 * @param string $key 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function __get($key)
	{
		if (array_key_exists($key, $this->_params))
		{
			return $this->_params[$key];
		}
		
		return NULL;
	}
	
	/**
	 * Set to params
	 *
	 * @param string $key 
	 * @param string $value 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function __set($key, $value)
	{
		$this->_params[$key] = $value;
	}
	
	/**
	 * PHP Magic Method::__isset()
	 *
	 * @param string $key 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function __isset($key)
	{
		return isset($this->_params[$key]);
	}
	
	/**
	 * Prints out the table if you don't do anything else.
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function __toString()
	{
		if ($this->render_started)
		{
			return '';
		}
		
		try {
			$start = microtime(TRUE);
			
			$return = $this->renderTable();
			
			$end = microtime(TRUE);
			
			$total_time = ($end-$start) . 's';
			
			return $return;
		} catch (Exception $e)
		{
			return "<pre>" . $e->getMessage() . "<br />" . $e->getTraceAsString() . "</pre>";
		}
		
	}
	
	/**
	 * Get the parameters in mass
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getParams()
	{
		return $this->_params;
	}
	
	/**
	 * Set the parameters in mass
	 *
	 * @param string $new_params 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function setParams($new_params)
	{
		if (!is_array($new_params))
		{
			throw new Memberfuse_Rest_Exception('Params must be an array.');
		}
		$this->_params = array_merge($this->_params, $new_params);
	}
	
	public function getAdapter()
	{
		return $this->_adapter;
	}
	
	public function setAdapter(LBHToolkit_TableMaker_Adapter_Interface $adapter)
	{
		$this->_adapter = $adapter;
	}
	
	
	/**
	 * setActionController()
	 *
	 * @param  Zend_Controller_Action $actionController
	 * @return Zend_Controller_ActionHelper_Abstract Provides a fluent interface
	 */
	public function setActionController(Zend_Controller_Action $actionController = null)
	{
		$return = parent::setActionController($actionController);
		
		$view = $actionController->view;
		
		$columns = $this->getColumns();
		
		if (count($columns))
		{
			foreach ($this->getColumns() AS $column)
			{
				$column->view = $view;
			}
		}
		
		return $return;
    }
	
	protected function _dataValue(&$data, $column, $value = NULL)
	{
		// Get the data value
		if (is_object($data) && isset($data->$column))
		{
			$value = (string)$data->$column;
		}
		else if (is_array($data) && isset($data[$column]))
		{
			$value = (string)$data[$column];
		}
		
		return $value;
	}
}
