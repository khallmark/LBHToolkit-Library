<?php
/**
 * Form.php
 * LBHToolkit_TableMaker_Search_Form
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
 * @since       2011-10-25
 * @package     LBHToolkit
 * @subpackage  LBHToolkit_TableMaker_Search
 * @copyright   Little Black Hat, 2011
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

class LBHToolkit_TableMaker_Search_Form extends LBHToolkit_Form_Abstract
{
	protected $_field_decorators = array(
		'ViewHelper',
		'Description',
		'Errors',
		array('Label', array()),
		array(
			array(
				'open-tag' => 'HtmlTag'
			), 
			array(
				'tag' => 'div',
				'openOnly' => TRUE, 
				'placement' => 'prepend'
			)
		),
		array(
			array(
				'close-tag' => 'HtmlTag'
			), 
			array(
				'tag' => 'div', 
				'closeOnly' => TRUE, 
				'placement' => 'append'
			)
		),
	);
	
	public function init()
	{
		parent::init();
		
		//$this->setElementDecorators($this->_field_decorators);
		
		$this->setMethod('get');
	}
	
	public function processSubmit()
	{
		// All the processing for this form is handled in the TableMaker adapter
	}
	
	public function addSubmit($label = 'Search')
	{
		$decorators = array(
			'ViewHelper',
			array(
				array(
					'open-tag' => 'HtmlTag'
				), 
				array(
					'tag' => 'span', 
					'class' => 'submit_btn',
				)
			),
		);
		
		$this->addElement(
			'submit', 
			$label, 
			array(
				'decorators' => $decorators,
			)
		);
	}
}