<?php

namespace SilverCommerce\Reports\Items;

use DateTime;
use SilverStripe\ORM\DB;
use SilverStripe\View\ViewableData;
use SilverCommerce\OrdersAdmin\Model\Invoice;
use SilverCommerce\Discounts\DiscountFactory;
use SilverCommerce\Discounts\Model\AppliedDiscount;
use SilverStripe\ORM\ArrayList;

class DiscountUsageItem extends ViewableData
{
    /**
     * Code to check against
     *
     * @var string
     */
    private $code;

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
        'Code' => 'Varchar',
        'Title' => 'Varchar',
        'DiscountValue' => 'Currency',
        'CodeUses' => 'Int',
        'InvoiceCount' => 'Int',
        'InvoiceSubTotal' => 'Currency',
        'InvoiceTax' => 'Curency'
    ];

    public function getTitle()
    {
        $discount = $this->getDiscount();

        if (!isset($discount)) {
            return "";
        }

        return $discount->Title;
    }

    /**
     * Get the total number of invoices applicable for this report
     *
     * @return int
     */
    public function getDiscountValue()
    {
        $discounts = $this->getAppliedDiscounts();
        $total = 0;

        foreach ($discounts as $discount) {
            $total += $discount->Value;
        }

        return $total;
    }

    /**
     * Get the total number of uses for this code
     *
     * @
     */
    public function getCodeUses()
    {
        return $this->getAppliedDiscounts()->count();
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
    public function getInvoiceSubTotal()
    {
        $invoices = $this->getInvoices();
        $total = 0;

        foreach ($invoices as $invoice) {
            $total += $invoice->SubTotal;
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
     * Return the applicable discount for this code
     *
     * @return \SilverCommerce\Discounts\Model\Discount
     */
    protected function getDiscount()
    {
        return DiscountFactory::create($this->getCode())->getDiscount(false);
    }

    /**
     * Get a list of all applied discounts for the selected invoices
     *
     * @return \SilverStripe\ORM\DataList
     */
    protected function getAppliedDiscounts()
    {
        $invoices = $this->getInvoices();
        $code = $this->getCode();

        if (!$invoices->exists()) {
            return ArrayList::create();
        }

        return AppliedDiscount::get()->filter([
            'Estimate.ID' => $invoices->columnUnique(),
            'Code' => $code
        ]);
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
        $statuses = Invoice::config()->paid_statuses;
        $code = $this->getCode();

        $field = $db->formattedDatetimeClause(
            '"Estimate"."StartDate"',
            $format
        );

        $date_filter = [
            $field . ' <= ?' =>  $end->format("Y-m-d"),
            $field . ' >= ?' =>  $start->format("Y-m-d")
        ];

        return Invoice::get()
            ->filter([
                'Status' => $statuses,
                'Discounts.Code' => $code
            ])->where($date_filter);
    }

    public function canView($member = null)
    {
        return true;
    }

    /**
     * Get the discount code for this item
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the discount code assigned to this item
     *
     * @param string $code
     *
     * @return self
     */
    public function setCode(string $code)
    {
        $this->code = $code;
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
