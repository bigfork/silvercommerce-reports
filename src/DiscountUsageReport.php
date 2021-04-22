<?php

namespace SilverCommerce\Reports;

use DateTime;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Reports\Report;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverCommerce\Discounts\Model\DiscountCode;
use SilverCommerce\Discounts\Model\AppliedDiscount;
use SilverCommerce\Reports\Items\DiscountUsageItem;

/**
 * Simple report showing all sales over a period of time
 *
 */
class DiscountUsageReport extends Report
{

    public function title()
    {
        return _t(
            __CLASS__ . ".Title",
            "Discount Usage"
        );
    }

    public function description()
    {
        return _t(
            __CLASS__ . ".Description",
            "View reports on discounts used on this site"
        );
    }

    public function columns()
    {
        return [
            'Code' => _t(DiscountUsageItem::class . 'Code', 'Code'),
            'Title' => _t(DiscountUsageItem::class . 'Title', 'Title'),
            'CodeUses' => _t(DiscountUsageItem::class . 'CodeUses', 'Code Uses'),
            'DiscountValue' => _t(DiscountUsageItem::class . 'DiscountValue', 'Total Discount'),
            'InvoiceCount' => _t(DiscountUsageItem::class . 'InvoiceCount', 'Number of Invoices'),
            'InvoiceSubTotal' => _t(DiscountUsageItem::class . 'InvoiceTotal', 'Total Sales (ex. Tax)'),
            'InvoiceTax' => _t(DiscountUsageItem::class . 'InvoiceTax', 'Total Tax')
        ];
    }

    public function exportColumns()
    {
        // Loop through all colls and replace BR's with spaces
        $cols = array();

        foreach ($this->columns() as $key => $value) {
            $cols[$key] = str_replace('<br/>', ' ', $value);
        }

        return $cols;
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $request = Injector::inst()->get(HTTPRequest::class);
        $params = $request->getVar("filters");
        $start = null;
        $end = null;
        $list = ArrayList::create();

        if ($params && array_key_exists("StartDate", $params)) {
            $start = new DateTime($params["StartDate"]);
        } else {
            $start = new DateTime();
            $start->modify('-30 days');
        }

        if ($params && array_key_exists("EndDate", $params)) {
            $end = new DateTime($params["EndDate"]);
        } else {
            $end = new DateTime();
        }

        // Get all codes in the system
        $codes = DiscountCode::get();
        
        foreach ($codes as $code) {
            $item = DiscountUsageItem::create()
                ->setCode($code->Code)
                ->setStartDate($start)
                ->setEndDate($end);

            $list->add($item);
        }

        return $list;
    }

    public function parameterFields()
    {
        $fields = FieldList::create();

        $fields->push(DateField::create("StartDate", "Start"));
        $fields->push(DateField::create("EndDate", "End"));

        return $fields;
    }
}
