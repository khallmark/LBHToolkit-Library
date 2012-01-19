<?php
/**
 * Text.php
 * LBHToolkit_Form_Element_Text
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
 * @since       2011-09-15
 * @package     LBHToolkit
 * @subpackage  Form
 * @copyright   Little Black Hat, 2011
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

class LBHToolkit_Form_Element_Text extends Zend_Form_Element_Text
{
	/*
	protected $_decorators = array(
		'ViewHelper',
		'Errors',
		'Description',
//		array('Label', array()),
/*		,
		array(
			
		),*/
	//);
	
	/**
	 * Load default decorators
	 *
	 * @return Zend_Form_Element
	 */
	public function loadDefaultDecorators()
	{
		if ($this->loadDefaultDecoratorsIsDisabled()) {
			return $this;
		}

		$decorators = $this->getDecorators();
		if (empty($decorators)) {
			$getId = create_function('$decorator',
									 'return $decorator->getElement()->getId()
											 . "-element";');
			$this->addDecorator('ViewHelper')
					->addDecorator('Errors')
					->addDecorator('Description', array('tag' => 'div', 'class' => 'description'))
					->addDecorator(
						'Label', 
						array()
					)
					->addDecorator(
						array('opentag' => 'HtmlTag'), 
						array(
							'tag' => 'div',
							'openOnly' => TRUE, 
							'placement' => 'prepend',
							'id'  => array('callback' => $getId)
						)
					)
					->addDecorator(
						array('close-tag' => 'HtmlTag'), 
						array(
							'tag' => 'div', 
							'closeOnly' => TRUE, 
							'placement' => 'append'
						)
					);
		}
		return $this;
	}

	/*
	$getId = create_function('$decorator',
                             'return $decorator->getElement()->getId()
                                     . "-element";');
    $this->addDecorator('ViewHelper')
         ->addDecorator('Errors')
         ->addDecorator('Description', array('tag' => 'p', 'class' => 'description'))
         ->addDecorator('HtmlTag', array('tag' => 'dd',
                                         'id'  => array('callback' => $getId)))
         ->addDecorator('Label', array('tag' => 'dt'));
	*/
}