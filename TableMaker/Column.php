<?php
/**
 * Header.php
 * LBHToolkit_TableMaker_Header
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
 * @subpackage  TableMaker
 * @copyright   Little Black Hat, 2011
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

class LBHToolkit_TableMaker_Column extends LBHToolkit_TableMaker_Abstract
{	
	protected $_tableMaker = NULL;
		
	public function setDefaultParams()
	{
		$this->search_type = '=';
		
		$decorator = new LBHToolkit_TableMaker_Decorator_Tag_Table_Header();
		
		$this->addDecorator('th', $decorator, 'header');
		
		
		$decorator = new LBHToolkit_TableMaker_Decorator_Tag_Table_Cell();
		
		$this->addDecorator('td', $decorator, 'body');
		
	}
	
	/**
	 * Takes an array of parameters and validates them. Called from the constructor
	 *
	 * @param string $params 
	 * @return void
	 * 
	 * @author Kevin Hallmark
	 */
	public function validateParams($params)
	{
		// Check that the column_id field is set (it's required)
		if (!$this->column_id)
		{
			throw new LBHToolkit_TableMaker_Exception('No column_id provided');
		}
		
		// Make sure the label is set, if it isn't, use an inflection of the id
		if ($this->label === NULL)
		{
			$this->label = ucwords(str_replace('_', ' ', $this->column_id));
		}
		
		// If decorators were passed, run them through the decorator add function
		if ($decorators = $this->decorators)
		{
			$decorators = array_reverse($decorators);
			
			foreach ($decorators AS $name => $decorator)
			{
				$this->addDecorator($name, $decorator, 'body');
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
		
		$this->_validate($params);
	}
	
	/**
	 * This function should validate in subclasses
	 *
	 * @param array $params 
	 * @return void
	 * @author Kevin Hallmark
	 */
	protected function _validate($params = array())
	{
		
	}
	
	/**
	 * Adds a decorator to the column
	 *
	 * @param string $alias 
	 * @param string $decorator 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function addDecorator($alias, $decorator, $type = self::DEFAULT_DECORATOR_TYPE)
	{
		// This is here for backwards compatability, but the "array" method for 
		// defining decorators is now deprecated. 
		if (is_array($decorator))
		{
			if (!isset($decorator['type']))
			{
				throw new LBHToolkit_TableMaker_Exception(sprintf('No decorator type specified for %s', $alias));
			}
			
			switch ($decorator['type'])
			{
				case 'ViewHelper':
					$class_name = 'LBHToolkit_TableMaker_Decorator_ViewHelper';
					break;
				case 'Template':
					$class_name = 'LBHToolkit_TableMaker_Decorator_Template';
					break;
				case 'Function':
					$class_name = 'LBHToolkit_TableMaker_Decorator_Function';
					break;
				default:
					$class_name = $decorator['type'];
					break;
			}
			
			if (!class_exists($class_name))
			{
				throw new LBHToolkit_TableMaker_Exception("The decorator $class_name could not be found.");
			}
			
			$decorator = new $class_name($decorator);
		}
		
		return parent::addDecorator($alias, $decorator, $type);
	}
	
	/**
	 * Render the header for this data set
	 *
	 * @param array|object $data 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function renderHeader(&$data, LBHToolkit_TableMaker_Paging $pagingInfo, $arguments = array())
	{
		// Set the label
		$label = $this->label;
		
		// If there is a sort available, add a link
		if ($this->sort)
		{
			$label = sprintf('<a href="%s">%s</a>', $pagingInfo->renderHeader($this->sort, $pagingInfo), $label);
		}
		
		// Allow a custom function to process the header information
		if($this->header_function && method_exists($data, $this->header_function))
		{
			$function = $this->header_function;
			$label = $data->$function($label, $attribs);
		}
		
		if (($decorator = $this->getDecorator('th', 'header'))  && !$this->header_attributes_set)
		{
			$this->header_attributes_set = TRUE;
			
			$attribs = $this->getHeaderAttributes();
			$attribs['id'] = $this->column_id;
			
			foreach ($attribs AS $key => $value)
			{
				$decorator->addAttribute($key, $value);
			}
		}

		$header = $this->_processDecorators($label, 'header', $arguments);
		
		// Return the HTML
		return $header;
	}
	
	/**
	 * Render the main row for this data set
	 *
	 * @param string $data 
	 * @param LBHToolkit_TableMaker_PagingInfo $pagingInfo 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function render(&$data, LBHToolkit_TableMaker_Paging $pagingInfo, $arguments = array())
	{
		// Get the column id
		$column = $this->column_id;
		
		// Get the html attributes to add to this column
		if (($decorator = $this->getDecorator('td', 'body')) && !$this->body_attributes_set)
		{
			$this->body_attributes_set = TRUE;
			
			$attribs = $this->getBodyAttributes();
			$attribs['id'] = $this->column_id . '-%%id%%';
			
			foreach ($attribs AS $key => $value)
			{
				$decorator->addAttribute($key, $value);
			}
		}

		// vd($arguments);
		$html = $arguments['row_value'];
		
		$html = $this->_processDecorators($html, 'body', $arguments);
		
		//$html = '<td' . $this->_parseAttribs($attribs) . '>' . $html . '</td>';
		
		return $html;
	}
	
	/**
	 * Create the form element for this column and add it to the passed in form
	 * object. 
	 *
	 * @param string $form 
	 * @param string $value 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function processSearchField(Zend_Form &$form, $value = NULL)
	{
		// If this field is not searchable, don't do anything
		if (!$this->isSearchable())
		{
			return;
		}
		
		// Get the search field
		$search_field = $this->search_field;
		
		// If it's a subclass of Zend_Form_Element_Abstract...
		if (is_a($search_field, 'Zend_Form_Element'))
		{
			// Add that element
			$element = $form->addElement($search_field);
		}
		else
		{
			// If it's only TRUE, do some default actions
			if (is_bool($search_field))
			{
				$search_field = array();
			}
			
			// The field name will be the column id
			$name = $this->column_id;
			
			// By default it's a text field
			$type = 'text';
			
			// If the 'type' field is set, use that instead
			if (isset($search_field['type']))
			{
				$type = $search_field['type'];
			}
			
			// If there is no label set, use the label for this column
			if (!isset($search_field['label']))
			{
				$search_field['label'] = $this->label;
			}
			
			// Set the value into the passed value
			$search_field['value'] = $value;
			
			// Add the form element
			$element = $form->addElement($type, $name, $search_field);
		}
		
		return $element;
	}
	
	/**
	 * Is this field searchable or not.
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function isSearchable()
	{
		if ($this->search_field !== NULL && $this->search_query !== NULL)
		{
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * This calculates any custom attributes you want on the header columns
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getHeaderAttributes()
	{
		// Add the default col scope
		$attribs = array('scope' => 'col');
		
		// If there is a header class set, add it to the attributes
		if ($this->header_class)
		{
			$attribs['class'] = $this->header_class;
		}
		
		// Return the default
		return $attribs;
	}
	
	/**
	 * This calculates any custom attributes you want on the body columns
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getBodyAttributes()
	{
		$attribs = array();
		
		// If there is a body class, set it
		if ($this->body_class)
		{
			$attribs['class'] = $this->body_class;
		}
		
		return $attribs;
	}
	
	
	/**
	 * Parse Attribtues arrays into a string
	 *
	 * @param string $attribs 
	 * @return void
	 * @author Kevin Hallmark
	 */
	protected function _parseAttribs($attribs)
	{
		$attrib_str = '';
		
		if (count($attribs))
		{
			foreach($attribs AS $key => $value)
			{
				$attrib_str = $attrib_str . ' ' . $key .'="' . $value . '"';
			}
		}
		
		return $attrib_str;
	}
	
	/**
	 * This function parses the parameters and replaces the special keys
	 *
	 * @param string $params 
	 * @param string $replacements 
	 * @return void
	 * @author Kevin Hallmark
	 */
	protected function _parseParams($params, $replacements)
	{
		if (count($params))
		{
			foreach ($params AS &$param)
			{
				if ($param == '%%row%%')
				{
					$param = $replacements['row'];
				}
				else if ($param == '%%row_value%%')
				{
					$param = $replacements['row_value'];
				}
			}
		}
		
		return $params;
	}
	
	public function getTableMaker()
	{
		return $this->_tableMaker;
	}
	
	public function setTableMaker(LBHToolkit_TableMaker $tableMaker)
	{
		$this->_tableMaker = $tableMaker;
	}
}