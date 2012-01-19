<?php
/**
 * TableMaker.php
 * LBHToolkit_TableMaker_Adapter_Doctrine
 * 
 * The TableMaker Doctrine Adapter can use a Doctrine_Query object to generate a
 * table.
 * 
 * LICENSE
 * 
 * This file is subject to the New BSD License that is bundled with this package.
 * It is available in the LICENSE file. 
 * 
 * It is also available online at http://www.littleblackhat.com/lbhtoolkit
 * 
 * @author		Kevin Hallmark <kevin.hallmark@littleblackhat.com>
 * @since		2011-08-24
 * @package		LBHToolkit
 * @subpackage	TableMaker
 * @copyright	Little Black Hat, 2011
 * @license		http://www.littleblackhat.com/lbhtoolkit	New BSD License
 */

class LBHToolkit_TableMaker_Adapter_Doctrine extends LBHToolkit_TableMaker_Adapter_Abstract
{
	/**
	 * Set the default parameters for the Adapter
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function setDefaultParams()
	{
		$this->primary_key = 'id';
	}
	
	/**
	 * Takes an array of parameters and validates them. Called from the constructor
	 *
	 * @param string $params 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function validateParams($params)
	{
		
	}
	
	
	/**
	 * Set the data into this adapter
	 *
	 * @param string $data 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function setData($query)
	{
		if (!is_a($query, "Doctrine_Query"))
		{
			throw new LBHToolkit_TableMaker_Exception("Query is not a valid Doctrine_Query subclass");
		}
		
		$this->query = $query;
	}
	
	/**
	 * Use the paging info from the TableMaker to get the specific result page.
	 *
	 * @param LBHToolkit_TableMaker_Paging $pagingInfo
	 * @return mixed Any interable collection of objects
	 * 
	 * @author Kevin Hallmark
	 */
	public function getData(LBHToolkit_TableMaker_Paging $pagingInfo)
	{
		$query = $this->query;
		
		// Add the order by clause
		$query->orderBy($pagingInfo->sort . ' ' . $pagingInfo->order);
		
		// Set the limit and the offset
		$query->offset($pagingInfo->count * ($pagingInfo->page - 1));
		$query->limit($pagingInfo->count);

		// Return the query
		return $query->execute();
	}
	
	/**
	 * Get the total number of results for this adapter
	 *
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getTotalCount()
	{
		$query = clone $this->query;
		return $query->count();
		
		$query->select($query->expr()->count('book'));
		
		$count = $query->getQuery()->getSingleScalarResult();
		
		return $count;
	}
	
	/**
	 * This function should return a unique/primary key for the passed in row.
	 *
	 * @param string $row 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function getPrimaryKey($row)
	{
		$primary_key = $this->primary_key;
		
		return $row->$primary_key;
	}
	
	/**
	 * Process the data based on the filters passed in the params.
	 *
	 * @param string $field 
	 * @param string $type 
	 * @param string $value 
	 * @return void
	 * @author Kevin Hallmark
	 */
	public function addFilter($query, $value)
	{
		if (strpos(strtolower($query), 'like') != FALSE)
		{
			$value = '%' . $value . '%';
		}
		
		$this->query->addWhere($query, $value);
	}
}