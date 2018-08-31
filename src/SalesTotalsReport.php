<?php

namespace SilverCommerce\Reports;

use SilverStripe\ORM\ArrayList;
use SilverCommerce\Reports\SalesReport;
use SilverStripe\ORM\FieldType\DBCurrency;
use SilverCommerce\Reports\Items\SalesTotalsReportItem;


/**
 * Provide top level totals of sales, orders, tax and postage
 * 
 * @package SilverCommerce
 * @subpackage Reports
 */
class SalesTotalsReport extends SalesReport
{
    public function title()
    {
        return _t(
            __CLASS__ . ".Title",
            "Sales Totals"
        );
    }

    public function description()
    {
        return _t(
            __CLASS__ . ".Description",
            "View total orders, values and tax in a given time"
        );
    }

    public function columns()
    {
        return array(
            "Title" => "StockID",
            "Value" => "Value"
        );
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $list = parent::sourceRecords($params, $sort, $limit);
        $totals = ArrayList::create();

        // Setup total objects
        $total_orders = SalesTotalsReportItem::create();
        $total_orders->Title = _t(__CLASS__ . ".TotalOrders", "Total Orders");
        $total_orders->Value = $list->count();

        $total_value = SalesTotalsReportItem::create();
        $total_value->Title = _t(__CLASS__ . ".TotalValue", "Total Value");
        $total_value->Value = 0;

        $total_postage = SalesTotalsReportItem::create();
        $total_postage->Title = _t(__CLASS__ . ".TotalPostage", "Total Postage");
        $total_postage->Value = 0;

        $total_tax = SalesTotalsReportItem::create();
        $total_tax->Title = _t(__CLASS__ . ".TotalTax", "Total Tax");
        $total_tax->Value = 0;

        foreach ($list as $order) {
            $total_value->Value += $order->Total;
            $total_postage->Value += $order->PostagePrice;
            $total_tax->Value += $order->TaxTotal;
        }

        // Clean up value apperance
        $price = DBCurrency::create();

        $price->setValue($total_value->Value);
        $total_value->Value = $price->Nice();
        $price->setValue($total_postage->Value);
        $total_postage->Value = $price->Nice();
        $price->setValue($total_tax->Value);
        $total_tax->Value = $price->Nice();

        $totals->add($total_orders);
        $totals->add($total_value);
        $totals->add($total_postage);
        $totals->add($total_tax);

        return $totals;
    }
}
