<?php
/**
 * Function.php
 * LBHToolkit_TableMaker_Decorator_Function
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

class LBHToolkit_TableMaker_Decorator_Function extends LBHToolkit_TableMaker_Decorator_Abstract
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
		// Get the body function
		$function = $this->name;
		// vd($parameters);
	//	$options = array($parameters);
		if ($this->arguments)
		{
			// Use them
			$options = $this->_parseParams($this->arguments, $parameters);
		}
		// vdd($options);
		if (!is_callable($function))
		{
			$message = sprintf('The closure function could not be called.');
			throw new LBHToolkit_TableMaker_Exception($message);
		}
		
		$output = call_user_func_array($function, $options);
		
		return $output;
	}
}