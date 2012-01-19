<?php
/**
 * OfferSet.php
 * LBHToolkit_Service_Amazon_OfferSet
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
 * @since       2012-01-15
 * @package     LBHToolkit
 * @subpackage  LBHToolkit_Service_Amazon
 * @copyright   Little Black Hat, 2012
 * @license     http://www.littleblackhat.com/lbhtoolkit    New BSD License
 */

class LBHToolkit_Service_Amazon_OfferSet extends Zend_Service_Amazon_OfferSet
{
	public $MoreOffersUrl;
	
    /**
     * Parse the given Offer Set Element
     *
     * @param  DOMElement $dom
     * @return void
     */
    public function __construct(DOMElement $dom)
    {
	$doc = $dom->ownerDocument;
	$doc->formatOutput = TRUE;
	e(htmlentities($doc->saveXML()));
        $xpath = new DOMXPath($dom->ownerDocument);
        $xpath->registerNamespace('az', 'http://webservices.amazon.com/AWSECommerceService/2011-08-01');

        $offer = $xpath->query('./az:OfferSummary', $dom);
        if ($offer->length == 1) {
            $lowestNewPrice = $xpath->query('./az:OfferSummary/az:LowestNewPrice/az:Amount', $dom);
            if ($lowestNewPrice->length == 1) {
                $this->LowestNewPrice = (int) $xpath->query('./az:OfferSummary/az:LowestNewPrice/az:Amount/text()', $dom)->item(0)->data;
                $this->LowestNewPriceCurrency = (string) $xpath->query('./az:OfferSummary/az:LowestNewPrice/az:CurrencyCode/text()', $dom)->item(0)->data;
            }
            $lowestUsedPrice = $xpath->query('./az:OfferSummary/az:LowestUsedPrice/az:Amount', $dom);
            if ($lowestUsedPrice->length == 1) {
                $this->LowestUsedPrice = (int) $xpath->query('./az:OfferSummary/az:LowestUsedPrice/az:Amount/text()', $dom)->item(0)->data;
                $this->LowestUsedPriceCurrency = (string) $xpath->query('./az:OfferSummary/az:LowestUsedPrice/az:CurrencyCode/text()', $dom)->item(0)->data;
            }
            $this->TotalNew = (int) $xpath->query('./az:OfferSummary/az:TotalNew/text()', $dom)->item(0)->data;
            $this->TotalUsed = (int) $xpath->query('./az:OfferSummary/az:TotalUsed/text()', $dom)->item(0)->data;
            $this->TotalCollectible = (int) $xpath->query('./az:OfferSummary/az:TotalCollectible/text()', $dom)->item(0)->data;
            $this->TotalRefurbished = (int) $xpath->query('./az:OfferSummary/az:TotalRefurbished/text()', $dom)->item(0)->data;
        }
        $offers = $xpath->query('./az:Offers/az:Offer', $dom);
        if ($offers->length >= 1) {
            /**
             * @see Zend_Service_Amazon_Offer
             */
            require_once 'Zend/Service/Amazon/Offer.php';
            foreach ($offers as $offer) {
                $this->Offers[] = new Zend_Service_Amazon_Offer($offer);
            }
        }
		
		$this->MoreOffersUrl = (string) $xpath->query('./az:OfferSummary/az:LowestNewPrice/az:CurrencyCode/text()', $dom)->item(0)->data;

    }
}