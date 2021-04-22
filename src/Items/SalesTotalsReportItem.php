<?php

namespace SilverCommerce\Reports\Items;

use SilverStripe\Core\Injector\Injectable;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\View\ViewableData;


/**
 * Item that can be loaded into a sales report
 */
class SalesTotalsReportItem extends ViewableData
{
    public $Title;

    public $Details;

    public $Value;

    public function canView($member = null)
    {
        return true;
    }
}