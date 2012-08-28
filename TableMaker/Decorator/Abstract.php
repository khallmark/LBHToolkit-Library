<?php
/**
 * Abstract.php
 * LBHToolkit_TableMaker_Decorator_Abstract
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
 * @since       2011-10-19
 * @package     LBHToolkit
 * @subpackage  TableMaker
 * @copyright   Little Black Hat, 2011
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

abstract class LBHToolkit_TableMaker_Decorator_Abstract
	extends LBHToolkit_Base 
	implements LBHToolkit_TableMaker_Decorator_Interface
{
	protected function _parseParams($params, $replacements, $replace = FALSE)
	{
		$new_params = array();
		
		if (count($params))
		{
			foreach ($params AS &$param)
			{
				if ($param == '%%row%%')
				{
					$new_params['row'] = $replacements['row'];
					$param = $replacements['row'];
				}
				
				if ($param == '%%id%%')
				{
					$new_params['id'] = $replacements['id'];
					$param = $replacements['id'];
				}
				
				if ($param == '%%row_value%%')
				{
					$new_params['row_value'] = $replacements['row_value'];
					$param = $replacements['row_value'];
				}
				
				if ($param == '%%tablemaker%%')
				{
					$new_params['tablemaker'] = $replacements['tablemaker'];
					$param = $replacements['tablemaker'];
				}
				
				if ($param == '%%html%%')
				{
					$new_params['html'] = $replacements['html'];
					$param = $replacements['html'];
				}
			}
		}
		
		if ($replace)
		{
			return $params;
		}
		
		return $new_params;
	}
	
	public function preRender($output, array $parameters = array())
	{
		return $output;
	}
	
	public function postRender($output, array $parameters = array())
	{
		return $output;
	}
	
}