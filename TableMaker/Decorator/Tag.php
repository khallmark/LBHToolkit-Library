<?php
/**
 * Tag.php
 * LBHToolkit_TableMaker_Decorator_Tag
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
 * @since       2012-04-24
 * @package     LBHToolkit
 * @subpackage  LBHToolkit_TableMaker_Decorator
 * @copyright   Little Black Hat, 2012
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

class LBHToolkit_TableMaker_Decorator_Tag extends LBHToolkit_TableMaker_Decorator_Abstract
{
	/**
	 * The format function is used by TableMaker decorators to process the resulting
	 * string.
	 *
	 * @param string $output
	 * @param array $parameters 
	 * @return string The HTML string
	 * @author Kevin Hallmark
	 */
	public function format($output, array $parameters = array())
	{
		if ($this->render_time == 'normal')
		{
			return $this->_renderTag($output, $parameters);
		}
		
		return $output;
	}
	
	public function preRender($output, array $parameters = array())
	{
		if ($this->render_time == 'pre')
		{
			return $this->_renderTag($output, $parameters);
		}
		
		return $output;
	}
	
	public function postRender($output, array $parameters = array())
	{
		if ($this->render_time == 'post')
		{
			return $this->_renderTag($output, $parameters);
		}
		
		return $output;
	}
	
	protected function _renderTag($output, $parameters = array())
	{
		$attributes = $this->_parseAttribs($parameters);

		$tag = $this->tag;
		
		// Set the contents to the current string
		$contents = $output;
		
		// If custom contents were passed...
		if (!is_null($this->content))
		{
			// Use them instead
			$contents = $this->content;
		}
		
		$tag = '<' . $tag . $attributes . '>' . $contents . '</' . $tag . '>';
		
		// Based on the position, render the element
		switch($this->position)
		{
			case 'prepend':
				$output = $tag . $output;
				break;
			case 'append':
				$output = $output . $tag;
				break;
			case 'wrap':
			default:
				$output = $tag;
				break;
			
		}
		
		return $output;
	}
	
	/**
	 * Set some default parameters
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function setDefaultParams()
	{
		$this->position = 'wrap';
		
		$this->render_time = 'normal';
	}
	
	/**
	 * Validate the default parameters
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function validateParams()
	{
		// Validate that a 'tag' was passed
		if (!$this->tag)
		{
			throw new Memberfuse_Menu_Decorator_Exception(
				'No tag provided for the tag decorator'
			);
		}
		
		// Get an array of valid positions
		$positions = array('prepend', 'append', 'wrap');
		
		// Validate the position
		if (!in_array($this->position, $positions))
		{
			throw new Memberfuse_Menu_Decorator_Exception(
				sprintf('Invalid position %s provided.', $this->position)
			);
		}
		
		// If the position is wrap, make sure the user did not enter custom contents
		if ($this->contents && $this->position == 'wrap')
		{
			throw new Memberfuse_Menu_Decorator_Exception(
				'Cannot include contents for wrapping tags'
			);
		}
	}
	
	/**
	 * Adds a new attribute, or appends the value if $append is true.
	 *
	 * @param string $name 
	 * @param string $value 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function addAttribute($name, $value, $overwrite = FALSE)
	{
		$attributes = $this->attributes;
		
		if (!$attributes)
		{
			$attributes = array();
		}
		
		if ($overwrite)
		{
			unset($attributes[$name]);
		}
		
		if (!isset($attributes[$name]))
		{
			$attributes[$name] = array($value);
		}
		else
		{
			if (!is_array($attributes[$name]))
			{
				$attributes[$name] = array($attributes[$name]);
			}

			$attributes[$name][] = $value;
		}

		
		$this->attributes = $attributes;
	}
	
	/**
	 * Parse Attribtues arrays into a string
	 *
	 * @param string $attribs 
	 * @return void
	 * @author Kevin Hallmark
	 */
	protected function _parseAttribs($arguments = array(), $attributes = NULL)
	{
		if ($attributes === NULL)
		{
			$attributes = $this->attributes;
		}
		
		$attribs = $this->_prepareAttribs($arguments, $attributes);
		
		$attrib_str = '';
		
		if (count($attribs))
		{
			foreach($attribs AS $key => $value)
			{
				$attrib_str = $attrib_str . sprintf(' %s="%s"', $key, $value);
			}
		}
		
		return $attrib_str;
	}
	
	protected function _prepareAttribs($arguments, $attributes)
	{
		if (count($attributes))
		{
			foreach ($attributes AS $key => &$value)
			{
				if (is_array($value))
				{
					$value = implode(' ', $value);
				}
				
				if (strpos($value, '%%table_name%%') !== FALSE)
				{
					$value = str_replace('%%table_name%%', $arguments['tablemaker']->table_name, $value);
				}
				
				if (strpos($value, '%%id%%') !== FALSE)
				{
					$value = str_replace('%%id%%', $arguments['id'], $value);
				}
				
				if (strpos($value, '%%html%%') !== FALSE)
				{
					$value = str_replace('%%html%%', $arguments['html'], $value);
					
				}
			}
		}
		
		return $attributes;
	}
}