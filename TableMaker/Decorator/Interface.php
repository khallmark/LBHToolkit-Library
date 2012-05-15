<?php
/**
 * Interface.php
 * LBHToolkit_TableMaker_Decorator_Interface
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

interface LBHToolkit_TableMaker_Decorator_Interface
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
	public function format($output, array $parameters = array());
	
	/**
	 * This function is executed before a table renders a collection of rows. This
	 * is not called by columns in any way.
	 *
	 * @param string $output 
	 * @param array $parameters 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function preRender($output, array $parameters = array());
	
	/**
	 * This function is executed after a table renders a collection of rows. This
	 * is not called by columns in any way.
	 *
	 * @param string $output 
	 * @param array $parameters 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function postRender($output, array $parameters = array());
}