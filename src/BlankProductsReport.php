<?php

namespace SilverCommerce\Reports;

use SilverStripe\Reports\Report;
use SilverStripe\Subsites\Model\Subsite;
use SilverCommerce\CatalogueAdmin\Model\CatalogueProduct;

class BlankProductsReport extends Report
{
    public function title()
    {
        return _t(
            __CLASS__ . ".Title",
            "Blank Products"
        );
    }

    public function description()
    {
        return _t(
            __CLASS__ . ".Description",
            "View all blank products on this site."
        );
    }

    public function sourceRecords($params, $sort, $limit)
    {
        if (class_exists(Subsite::class)) {
            $list = Subsite::get_from_all_subsites(
                CatalogueProduct::class, 
                [
                    'Content' => null
                ]
            );
        } else {
            $list = CatalogueProduct::get()->filter('Content', null);
        }

        return $list;
    }

    // which fields on that object we want to show
    public function columns() 
    {
        $fields = [
            "CMSThumbnail"          => "Thumbnail",
            "ClassName"             => "Product",
            "StockID"               => "StockID",
            "Title"                 => "Title",
            "BasePrice"             => "Price",
            "TaxRate"               => "Tax Percent",
            "CategoriesList"        => "Categories",
            "TagsList"              => "Tags",
            "Disabled"              => "Disabled"            
        ];

        return $fields;
    }
}