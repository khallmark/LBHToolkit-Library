<?php
/**
 * Abstract.php
 * LBHToolkit_TableMaker_Abstract
 * 
 * <description>
 * 
 * LICENSE
 * 
 * This file is subject to the New BSD License that is bundled with this package.
 * It is available in the LICENSE file. 
 * 
 * It is also available online at http://www.littleblackhat.com/lbhtoolkit
 * 
 * @author      Kevin Hallmark <kevin.hallmark@littleblackhat.com>
 * @since       2011-08-24
 * @package     LBHToolkit
 * @subpackage  LBHToolkit_TableMaker
 * @copyright   Little Black Hat, 2011
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

abstract class LBHToolkit_TableMaker_Abstract extends LBHToolkit_Base implements LBHToolkit_TableMaker_Interface
{
	protected $_decorators = array();
	
	protected $_decorator_types = array('header', 'body');
	
	/**
	 * Added to make changing the default decorator type easier
	 */
	const DEFAULT_DECORATOR_TYPE = 'body';
	
	/**
	 * The defaut page size for a tablemaker result set
	 */
	const PAGE_SIZE = 20;
	const PAGE_SIZE_MAX = 100;
	
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
				
				if (isset($arguments['html']))
				{
					$arguments['html'] = $html;
				}
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
	 * Serialize 
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function toArray()
	{
		$params              = $this->_params;
		$params['className'] = $this->_className;
		
		// Make sure we don't cache our Request Parameters
		$request_params = array('action', 'relationship', 'page', 'count');
		foreach($request_params AS $key_value)
		{
			unset($request_params);
		}
		
		if($custom_fields = $this->customSerializeFields())
		{
			$params = array_merge($params, $custom_fields);
		}
		
		return $params;
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