<?php

namespace SilverCommerce\Reports;

use DateTime;
use SilverStripe\ORM\DB;
use SilverStripe\Reports\Report;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverCommerce\OrdersAdmin\Model\Invoice;

/**
 * Simple report showing all sales over a period of time
 *
 */
class SalesReport extends Report
{

    public function title()
    {
        return _t(
            __CLASS__ . ".Title",
            "Sales Made"
        );
    }

    public function description()
    {
        return _t(
            __CLASS__ . ".Description",
            "View reports on all orders made through this site"
        );
    }

    public function columns()
    {
        return array(
            'Number' => '#',
            'Status' => 'Status',
            'StartDate' => 'Date',
            'SubTotal.Nice' => 'Sub Total',
            'PostagePrice.Nice' => 'Postage',
            'TaxTotal.Nice' => 'Tax',
            'Total.Nice' => 'Total',
            'FirstName' => 'First Name(s)',
            'Surname' => 'Surname',
            'Email' => 'Email Address'
        );
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
        $statuses = Invoice::config()->paid_statuses;
        $db = DB::get_conn();
        $start = null;
        $end = null;
        $date_filter = null;

        if ($params && array_key_exists("StartDate", $params)) {
            $start = new DateTime($params["StartDate"]);
        }

        if ($params && array_key_exists("EndDate", $params)) {
            $end = new DateTime($params["EndDate"]);
        }

        $format = "%Y-%m-%d";
        $start_field = $db->formattedDatetimeClause(
            '"Estimate"."StartDate"',
            $format
        );

        if ($start && $end) {
            $date_filter = [
                $start_field . ' <= ?' =>  $end->format("Y-m-d"),
                $start_field . ' >= ?' =>  $start->format("Y-m-d")
            ];
        }

        $list = Invoice::get();

        if ($date_filter) {
            $list = $list->where($date_filter);
            return $list->filter("Status", $statuses);
        }

        // If no filters, return empty list
        return $list->filter("ID", 0);
    }

    public function parameterFields()
    {
        $fields = FieldList::create();

        $fields->push(DateField::create("StartDate", "Start"));
        $fields->push(DateField::create("EndDate", "End"));

        return $fields;
    }
}
