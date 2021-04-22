<?php

namespace SilverCommerce\Reports\Items;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\View\ViewableData;


/**
 * Item that can be loaded into an OrderItem report
 */
class ItemsOrderedReportItem extends ViewableData
{
    public $StockID;
    public $Details;
    public $Price;
    public $Quantity;

    public function canView($member = null)
    {
        return true;
    }
}