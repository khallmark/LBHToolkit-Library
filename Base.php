<?php
/**
 * Base.php
 * LBHToolkit_Base
 * 
 * This file handles the most basic setup for many LBHToolkit Classes.
 * 
 * LICENSE
 * 
 * This file is subject to the New BSD License that is bundled with this package.
 * It is available in the LICENSE file. 
 * 
 * It is also available online at http://www.littleblackhat.com/lbhtoolkit
 * 
 * @author      Kevin Hallmark <khallmark@avectra.com>
 * @since       2011-10-19
 * @package     LBHToolkit
 * @subpackage  LBHToolkit
 * @copyright   Little Black Hat, 2011
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

abstract class LBHToolkit_Base implements Serializable
{
	/**
	 * Parameters used by this object
	 *
	 * @var array
	 */
	protected $_params = array();
	
	/**
	 * Constructor
	 *
	 * @param string $object 
	 * @param string $params 
	 * @author Kevin Hallmark
	 */
	public function __construct($params = NULL)
	{
		if (method_exists($this, 'setDefaultParams'))
		{
			$this->setDefaultParams();
		}
		
		if ($params !== NULL)
		{
			$this->setParams($params, TRUE);
		}
		
		if (method_exists($this, 'validateParams'))
		{
			$this->validateParams($params);
		}
	}
	
	
	
	/**
	 * Get a value from the params array
	 *
	 * @param string $key 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function __get($key)
	{
		return $this->getParam($key);
	}
	
	public function getParam($key)
	{
		if (array_key_exists($key, $this->_params))
		{
			return $this->_params[$key];
		}
		
		return NULL;
		return $this->_params[$key];
	}
	
	public function setParam($key, $value)
	{
		$validate_method = 'validate' . ucfirst($key);
		
		if (method_exists($this, $validate_method))
		{
			$valid = $this->$validate_method($value);
			if ($valid !== TRUE)
			{
				$message = sprintf('Validation failed for %s with reason %s.', $key, $valid);
				throw new LBHToolkit_Exception($message);
			}
		}
		
		$this->_params[$key] = $value;
	}
	
	/**
	 * Set to params
	 *
	 * @param string $key 
	 * @param string $value 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function __set($key, $value)
	{
		$this->setParam($key, $value);
	}
	
	/**
	 * PHP Magic Method::__isset()
	 *
	 * @param string $key 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function __isset($key)
	{
		return isset($this->_params[$key]);
	}
	
	/**
	 * Get the parameters in mass
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getParams()
	{
		return $this->_params;
	}
	
	/**
	 * Set the parameters in mass
	 *
	 * @param string $new_params 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function setParams($new_params, $merge = FALSE)
	{
		if (!is_array($new_params))
		{
			throw new Memberfuse_Rest_Doctrine_Exception('Params must be an array.');
		}
		
		if ($merge)
		{
			$this->_params = array_merge($this->_params, $new_params);
		}
		else
		{
			$this->_params = $new_params;
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/**
	 * Serialize 
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function serialize()
	{
		$params = $this->toArray();
		
		return serialize($params);
	}
	
	public function toArray()
	{
		$params = $this->_params;
		
		if($custom_fields = $this->customSerializeFields())
		{
			$params = array_merge($params, $custom_fields);
		}
		
		return $params;
	}

	/**
	 * Custom Unserialize
	 *
	 * @param string $data 
	 * @return void
	 * @author Kevin Hallmark
	 */
    public function unserialize($data)
	{
		$data = unserialize($data);
		
		$data = $this->customUnserializeFields($data);
		
		$this->setParams($data);
	}
	
	/**
	 * Lets you return custom fields to serialize
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function customSerializeFields()
	{
		return array();
	}
	
	/**
	 * Lets you add custom field processing on unserialize
	 *
	 * @param string $params 
	 * @return array Modified Parameters array
	 * @author Kevin Hallmark
	 */
	public function customUnserializeFields($params)
	{
		return $params;
	}
	
}