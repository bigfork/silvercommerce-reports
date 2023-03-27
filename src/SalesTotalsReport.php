<?php

namespace SilverCommerce\Reports;

use DateTime;
use SilverStripe\ORM\ArrayList;
use SilverCommerce\Reports\SalesReport;
use SilverStripe\ORM\FieldType\DBCurrency;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;
use SilverCommerce\Reports\Items\SalesTotalsReportItem;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;


/**
 * Provide top level totals of sales, orders, tax and postage
 * 
 * @package    SilverCommerce
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
        return [
            "Title" => _t(__CLASS__ . ".Report", "Report"),
            "Value" => _t(__CLASS__ . ".Current", "Current"),
            "Previous" => _t(__CLASS__ . ".PreviousYear", "Previous Year")
        ];
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $totals = ArrayList::create();
        $list = parent::sourceRecords($params, $sort, $limit);

        // Get a comparison list of the previous year
        if (array_key_exists("StartDate", $params)) {
            $start = new DateTime($params["StartDate"]);
            $start->modify('-1 year');
            $params['StartDate'] = $start->format('Y-m-d');
        }

        if (array_key_exists("EndDate", $params)) {
            $end = new DateTime($params["EndDate"]);
            $end->modify('-1 year');
            $params['EndDate'] = $end->format('Y-m-d');
        }

        $comparison = parent::sourceRecords($params, $sort, $limit);

        // Setup total objects
        $total_orders = SalesTotalsReportItem::create();
        $total_orders->Title = _t(__CLASS__ . ".TotalOrders", "Total Orders");
        $total_orders->Value = $list->count();
        $total_orders->Previous = $comparison->count();

        $gross_value = SalesTotalsReportItem::create();
        $gross_value->Title = _t(__CLASS__ . ".TotalGross", "Gross Total Sales (inc. Tax)");
        $gross_value->Value = 0;
        $gross_value->Previous = 0;

        $net_value = SalesTotalsReportItem::create();
        $net_value->Title = _t(__CLASS__ . ".TotalNet", "Net Total Sales (ex. Tax & Postage)");
        $net_value->Value = 0;
        $net_value->Previous = 0;

        $total_postage = SalesTotalsReportItem::create();
        $total_postage->Title = _t(__CLASS__ . ".TotalPostage", "Total Postage");
        $total_postage->Value = 0;
        $total_postage->Previous = 0;

        $total_tax = SalesTotalsReportItem::create();
        $total_tax->Title = _t(__CLASS__ . ".TotalTax", "Total Tax");
        $total_tax->Value = 0;
        $total_tax->Previous = 0;

        foreach ($list as $order) {
            $gross_value->Value += $order->Total;
            $net_value->Value += $order->SubTotal;
            $total_postage->Value += $order->PostagePrice;
            $total_tax->Value += $order->TaxTotal;
        }

        unset($order);

        foreach ($comparison as $order) {
            $gross_value->Previous += $order->Total;
            $net_value->Previous += $order->SubTotal;
            $total_postage->Previous += $order->PostagePrice;
            $total_tax->Previous += $order->TaxTotal;
        }

        unset($order);
        unset($list);
        unset($comparison);

        // Clean up value apperance
        $price = DBCurrency::create();

        $price->setValue($gross_value->Value);
        $gross_value->Value = $price->Nice();

        $price->setValue($gross_value->Previous);
        $gross_value->Previous = $price->Nice();

        $price->setValue($net_value->Value);
        $net_value->Value = $price->Nice();

        $price->setValue($net_value->Previous);
        $net_value->Previous = $price->Nice();

        $price->setValue($total_postage->Value);
        $total_postage->Value = $price->Nice();

        $price->setValue($total_postage->Previous);
        $total_postage->Previous = $price->Nice();

        $price->setValue($total_tax->Value);
        $total_tax->Value = $price->Nice();

        $price->setValue($total_tax->Previous);
        $total_tax->Previous = $price->Nice();

        $totals->add($total_orders);
        $totals->add($gross_value);
        $totals->add($net_value);
        $totals->add($total_postage);
        $totals->add($total_tax);

        return $totals;
    }

    /**
     * Return the parent GridField without column sorting
     *
     * @return \SilverStripe\Forms\FormField subclass
     */
    public function getReportField()
    {
        /**
 * @var \SilverStripe\Forms\GridField\GridField 
*/
        $grid_field = parent::getReportField();
        $grid_field->getConfig()
            ->removeComponentsByType(GridFieldSortableHeader::class)
            ->addComponent(new GridFieldTitleHeader());

        return $grid_field;
    }
}
