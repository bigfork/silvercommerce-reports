<?php

namespace SilverCommerce\Reports;

use DateTime;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Control\HTTPRequest;
use SilverCommerce\Reports\SalesReport;
use SilverStripe\Core\Injector\Injector;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverCommerce\ContactAdmin\Model\Contact;
use SilverCommerce\Reports\Items\CustomerOrdersItem;

class CustomerOrdersReport extends SalesReport
{
    public function title()
    {
        return _t(
            __CLASS__ . ".Title",
            "Customer Orders"
        );
    }

    public function description()
    {
        return _t(
            __CLASS__ . ".Description",
            "View a summary of customer orders"
        );
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $request = Injector::inst()->get(HTTPRequest::class);
        $params = $request->getVar("filters");
        $db = DB::get_conn();
        $start = null;
        $end = null;
        $date_filter = null;
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

        $contacts = Contact::get();
        $orders = Invoice::get();

        if ($date_filter) {
            $orders = $orders->where($date_filter);
        }

        foreach ($contacts as $contact) {
            $item = CustomerOrdersItem::create();
            $item->Surname = $contact->Surname;
            $item->Email = $contact->Email;
            $item->PhoneNumber = $contact->Phone;
            $total = 0;
            $c_orders = $orders->filter('CustomerID', $contact->ID);
            if ($c_orders->exists()) {
                foreach ($c_orders as $order) {
                    $total += $order->getTotal();
                }
            }
            $item->TotalOrderValue = $total;

            $list->add($item);
        }

        return $list;
    }

    // which fields on that object we want to show
    public function columns() 
    {
        $fields = [
            "Surname" => "Surname",
            "Email" => "Email",
            "PhoneNumber" => "PhoneNumber",
            "TotalOrderValue" => "TotalOrderValue"
        ];

        return $fields;
    }
}