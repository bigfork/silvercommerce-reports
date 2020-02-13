<?php

namespace SilverCommerce\Reports;

use DateTime;
use SilverStripe\ORM\DB;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Reports\Report;
use SilverStripe\Forms\DateField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverCommerce\ContactAdmin\Model\Contact;
use SilverCommerce\Reports\Items\CustomerOrdersItem;
use SilverStripe\Forms\GridField\GridFieldSortableHeader;
use SilverStripe\Security\Member;
use Symbiote\GridFieldExtensions\GridFieldTitleHeader;

class CustomerOrdersReport extends Report
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
            "View a summary of customer orders within the time period"
        );
    }

    public function columns()
    {
        $singleton = Contact::singleton();

        return array(
            'FirstName' => _t(CustomerOrdersItem::class . 'FirstName', 'First Name'),
            'Surname' => _t(CustomerOrdersItem::class . 'Surname', 'Surname'),
            'Email' => _t(CustomerOrdersItem::class . 'Email', 'Email'),
            'InvoiceCount' => _t(CustomerOrdersItem::class . 'InvoiceCount', 'Total Invoices'),
            'InvoiceTotal' => _t(CustomerOrdersItem::class . 'InvoiceTotal', 'Total Invoice Value'),
            'InvoiceTax' => _t(CustomerOrdersItem::class . 'InvoiceTax', 'Total Invoice Tax')
        );
    }

    public function sourceRecords($params, $sort, $limit)
    {
        $request = Injector::inst()->get(HTTPRequest::class);
        $params = $request->getVar("filters");
        $db = DB::get_conn();
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

        $format = "%Y-%m-%d";
        $field = $db->formattedDatetimeClause(
            '"Estimate"."StartDate"',
            $format
        );
        $date_filter = [
            $field . ' <= ?' =>  $end->format("Y-m-d"),
            $field . ' >= ?' =>  $start->format("Y-m-d")
        ];

        $invoices = Invoice::get()->where($date_filter);
        $ids = $invoices->column('CustomerID');

        // If no invoices available, set a null value (to return an empty list)
        if (count($ids) === 0) {
            $ids = 0;
        }

        $contacts = Contact::get()->filter('ID', $ids);

        // Loop through relevent contacts and create/assign relevent objects
        foreach ($contacts as $contact) {
            $item = CustomerOrdersItem::create()
                ->setContact($contact)
                ->setStartDate($start)
                ->setEndDate($end);

            $list->add($item);
        }

        return $list->sort("InvoiceTotal DESC");
    }

    public function parameterFields()
    {
        $fields = FieldList::create();

        $fields->push(DateField::create("StartDate", "Start"));
        $fields->push(DateField::create("EndDate", "End"));

        return $fields;
    }

    /**
     * Return the parent GridField without column sorting
     *
     * @return \SilverStripe\Forms\FormField subclass
     */
    public function getReportField()
    {
        /** @var \SilverStripe\Forms\GridField\GridField */
        $grid_field = parent::getReportField();
        $grid_field->getConfig()
            ->removeComponentsByType(GridFieldSortableHeader::class)
            ->addComponent(new GridFieldTitleHeader());

        return $grid_field;
    }
}
