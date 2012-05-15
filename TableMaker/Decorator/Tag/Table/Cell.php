<?php
/**
 * Cell.php
 * LBHToolkit_TableMaker_Decorator_Tag_Table_Cell
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
 * @subpackage  LBHToolkit_TableMaker_Decorator_Tag_Table
 * @copyright   Little Black Hat, 2012
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

class LBHToolkit_TableMaker_Decorator_Tag_Table_Cell extends LBHToolkit_TableMaker_Decorator_Tag
{
	/**
	 * Set some default parameters
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function setDefaultParams()
	{
		parent::setDefaultParams();

		$this->tag = 'td';
	}
}