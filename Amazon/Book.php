<?php
/**
 * Book.php
 * LBHToolkit_Amazon_Book
 * 
 * Encapsulates interactions with the Amazon Product Avertising API
 * 
 * LICENSE
 * 
 * This file is subject to the New BSD License that is bundled with this package.
 * It is available in the LICENSE file. 
 * 
 * It is also available online at http://www.littleblackhat.com/lbhtoolkit
 * 
 * @author      Kevin Hallmark <kevin.hallmark@littleblackhat.com>
 * @since       2011-08-24
 * @package     LBHToolkit
 * @subpackage  Amazon
 * @copyright   Little Black Hat, 2011
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

class LBHToolkit_Amazon_Book
{
	protected static $_offer_types = array('New', 'Used');
	
	public static $associateTag = 'billy-bob';
	
	/**
	 * undocumented variable
	 *
	 * @var string
	 */
	protected $_book = NULL;
	
	/**
	 * Finds all books in the amazon system by ISBN 13. Returns an array of results.
	 *
	 * @param string $isbn13 
	 * @return array
	 * @author Kevin Hallmark
	 */
	public static function findByIsbn13($isbn13, $params = array())
	{
		// Get the Amazon service singleton
		$amazon = LBHToolkit_Amazon::getService();
		
		$default_params = array(
			'SearchIndex' => 'Books',
			'Keywords' => $isbn13,
			'ResponseGroup' => 'ItemAttributes',
			'AssociateTag' => self::$associateTag,
		);
		
		$params = array_merge($default_params, $params);
		
		// Find the book, searching the keywords for the ISBN
		$results = $amazon->itemSearch($params);
		//vdd($results->totalResults());
		// Initialize the matches
		$matches = array();
		
		// Loop through the results
		foreach ($results as $result)
		{
			// Check the EAN (Amazon's name for ISBN13)
			if ($result->EAN == $isbn13)
			{
				// Add it to the matches
				$matches[] = new LBHToolkit_Amazon_Book($result);
			}
		}
		
		return $matches;
	}
	
	public static function getByASIN($asin, $params = array())
	{
		$amazon = LBHToolkit_Amazon::getService();
		
		$default_params = array(
			'IdType' => 'ASIN',
			'ResponseGroup' => 'ItemAttributes',
			'AssociateTag' => self::$associateTag,
		);
		
		$params = array_merge($default_params, $params);
		//var_dump($params);
		$book = $amazon->itemLookup($asin, $params);
		
		return new LBHToolkit_Amazon_Book($book);
	}
	
	public function __construct(Zend_Service_Amazon_Item $book)
	{
		$this->_book = $book;
	}
	
	public function newOffers($count = 10)
	{
		return $this->_loadOffers('New', $count);
	}
	
	public function usedOffers($count = 10)
	{
		return $this->_loadOffers('Used', $count);
	}
	
	protected function _loadOffers($type, $count = 15)
	{
		$offer_result = LBHToolkit_Amazon_Book::getByASIN(
			$this->ASIN, 
			array(
				'ResponseGroup' => 'OfferFull',
				//'Condition' => 'Used', 
				//'MerchantId' => 'All',
			)
		);
		//var_dump($this->_book);
		//var_dump($offer_result);
		//var_dump($offer_result->Offers->MoreOffersUrl);
		
		$offer_url = $offer_result->Offers->MoreOffersUrl;
		
		$offer_html = file_get_contents($offer_url);
		libxml_use_internal_errors(TRUE);
		$doc = new DOMDocument();
		
        //$xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');
		$doc->loadHtml($offer_html);
		$xpath = new DOMXPath($doc);

		$elements = $xpath->query("//span[@class='price']");
		$prices = array();
		
		if (!is_null($elements)) {
			foreach ($elements as $element) {
				$nodes = $element->childNodes;
				
				foreach ($nodes as $node) {
					$prices[] = (float) trim($node->nodeValue, '$');
				}
		  }
		}
		
		$offers = $offer_result->Offers;
		
		if (count($offers) > $count)
		{
			$offers = array_slice($offers, 0, $count);
		}
		
		return $prices;	
	}
	
	public function __get($key)
	{
		if ($this->getBook()->$key !== NULL)
		{
			return $this->getBook()->$key;
		}
		
		return NULL;
	}
	
	protected function getBook()
	{
		return $this->_book;
	}
}
