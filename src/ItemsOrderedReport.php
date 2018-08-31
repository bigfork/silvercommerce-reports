<?php

namespace SilverCommerce\Reports;

use SilverStripe\ORM\ArrayList;
use SilverStripe\Reports\Report;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Forms\DropdownField;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\ORM\FieldType\DBDatetime;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverCommerce\Reports\Items\ItemsOrderedReportItem;

// Only load this if reports are active
class ItemsOrderedReport extends SalesReport
{
    public function title()
    {
        return _t(
            __CLASS__ . ".Title",
            "Items Ordered"
        );
    }

    public function description()
    {
        return _t(
            __CLASS__ . ".Description",
            "View all individual products ordered through this site"
        );
    }

    public function columns()
    {
        return array(
            "StockID" => "StockID",
            "Details" => "Details",
            "Price" => "Price",
            "Quantity" => "Quantity"
        );
    }

    public function sortColumns()
    {
        return array();
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $list = parent::sourceRecords($params, $sort, $limit);
        $return = ArrayList::create();

        if ($list->exists()) {
            foreach ($list as $order) {
                // Setup a filter for our order items
                $filter = array();

                if (!empty($params['Filter_ProductName'])) {
                    $filter["Title:PartialMatch"] = $params['Filter_ProductName'];
                }

                if (!empty($params['Filter_StockID'])) {
                    $filter["StockID"] = $params['Filter_StockID'];
                }

                $list = (count($filter)) ? $order->Items()->filter($filter) : $order->Items();

                foreach ($list as $order_item) {
                    if ($order_item->StockID) {
                        if ($list_item = $return->find("StockID", $order_item->StockID)) {
                            $list_item->Quantity = $list_item->Quantity + $order_item->Quantity;
                        } else {
                            $report_item = ItemsOrderedReportItem::create();
                            $report_item->ID = $order_item->StockID;
                            $report_item->StockID = $order_item->StockID;
                            $report_item->Details = $order_item->Title;
                            $report_item->Price = $order_item->Price;
                            $report_item->Quantity = $order_item->Quantity;

                            $return->add($report_item);
                        }
                    }
                }
            }
        }

        $return = $return->sort("Quantity", "DESC");

        return $return;
    }

    public function parameterFields()
    {
        $fields = parent::parameterFields();

        $fields->push(TextField::create(
            'Filter_StockID',
            'Stock ID'
        ));
        
        $fields->push(TextField::create(
            'Filter_ProductName',
            'Product Name'
        ));

        return $fields;
    }
}
