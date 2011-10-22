<?php
/**
 * Form.php
 * LBHToolkit_Form_Abstract
 * 
 * Provides default settings and functions on top of Zend_Form
 * 
 * LICENSE
 * 
 * This file is subject to the New BSD License that is bundled with this package.
 * It is available in the LICENSE file. 
 * 
 * It is also available online at http://www.littleblackhat.com/lbhtoolkit
 * 
 * @author      Kevin Hallmark <kevin.hallmark@littleblackhat.com>
 * @since       2011-09-15
 * @package     LBHToolkit
 * @subpackage  Form
 * @copyright   Little Black Hat, 2011
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

abstract class LBHToolkit_Form_Abstract extends Zend_Form
{
	public function init()
	{
		parent::init();
		
		$this->addPrefixPath('LBHToolkit_Validate', realpath(dirname(__FILE__)));
	}
	
	public abstract function processSubmit();
}
