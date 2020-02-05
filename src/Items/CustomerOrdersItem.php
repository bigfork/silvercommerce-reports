<?php

namespace SilverCommerce\Reports\Items;

use SilverStripe\View\ViewableData;

class CustomerOrdersItem extends ViewableData
{
    public $Surname;
    public $Email;
    public $PhoneNumber;
    public $TotalOrderValue;

    public function canView($member = null)
    {
        return true;
    }
}