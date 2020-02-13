<?php

namespace SilverCommerce\Reports\Items;

use DateTime;
use SilverStripe\ORM\DB;
use SilverStripe\View\ViewableData;
use SilverCommerce\ContactAdmin\Model\Contact;

class CustomerOrdersItem extends ViewableData
{
    /**
     * Customer object assigned to this item
     *
     * @var \SilverCommerce\ContactAdmin\Model\Contact
     */
    private $contact;

    /**
     * Start date for this item
     *
     * @var DateTime
     */
    private $start_date;

    /**
     * End date for this item
     *
     * @var DateTime
     */
    private $end_date;

    private static $casting = [
        'FirstName' => 'Varchar',
        'Surname' => 'Varchar',
        'Email' => 'Varchar',
        'InvoiceCount' => 'Int',
        'InvoiceTotal' => 'Currency',
        'InvoiceTax' => 'Curency'
    ];

    public function getFirstName()
    {
        return $this->getContact()->FirstName;
    }

    public function getSurname()
    {
        return $this->getContact()->Surname;
    }

    public function getEmail()
    {
        return $this->getContact()->Email;
    }

    /**
     * Get the total number of invoices applicable for this report
     *
     * @return int
     */
    public function getInvoiceCount()
    {
        return $this->getInvoices()->count();
    }

    /**
     * Get the total number of invoices applicable for this report
     *
     * @return int
     */
    public function getInvoiceTotal()
    {
        $invoices = $this->getInvoices();
        $total = 0;

        foreach ($invoices as $invoice) {
            $total += $invoice->Total;
        }

        return $total;
    }

    /**
     * Get the total number of invoices applicable for this report
     *
     * @return int
     */
    public function getInvoiceTax()
    {
        $invoices = $this->getInvoices();
        $total = 0;

        foreach ($invoices as $invoice) {
            $total += $invoice->TaxTotal;
        }

        return $total;
    }

    /**
     * Get the invoices for this contact (within the set timeframe)
     *
     * @return \SilverStripe\ORM\DataList
     */
    protected function getInvoices()
    {
        $db = DB::get_conn();
        $start = $this->getStartDate();
        $end = $this->getEndDate();
        $format = "%Y-%m-%d";
        $contact = $this->getContact();

        $field = $db->formattedDatetimeClause(
            '"Estimate"."StartDate"',
            $format
        );

        $date_filter = [
            $field . ' <= ?' =>  $end->format("Y-m-d"),
            $field . ' >= ?' =>  $start->format("Y-m-d")
        ];

        return $contact->Invoices()->where($date_filter);
    }

    public function canView($member = null)
    {
        return true;
    }

    /**
     * Get customer object assigned to this item
     *
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set customer object assigned to this item
     *
     * @param Contact $contact Customer object assigned to this item
     *
     * @return self
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get start date for this item
     *
     * @return DateTime
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * Set start date for this item
     *
     * @param DateTime $date Start date for this item
     *
     * @return self
     */
    public function setStartDate(DateTime $date)
    {
        $this->start_date = $date;

        return $this;
    }

    /**
     * Get end date for this item
     *
     * @return DateTime
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * Set end date for this item
     *
     * @param DateTime $end_date End date for this item
     *
     * @return self
     */
    public function setEndDate(DateTime $date)
    {
        $this->end_date = $date;

        return $this;
    }
}
