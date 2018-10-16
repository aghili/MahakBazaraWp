<?php

/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 10/7/2018
 * Time: 11:17 AM
 */
interface int_shop_product
{
    public function save();

    public function remove();

    public function set_by_bazara($bazara_product);

    #geters
    public function get_name();

    public function get_slug();

    public function get_date_created();

    public function get_date_modified();

    public function get_status();

    public function get_featured();

    public function get_catalog_visibility();

    public function get_description();

    public function get_short_description();

    public function get_sku();

    public function get_price();

    public function get_regular_price();

    public function get_sale_price();

    public function get_date_on_sale_from();

    public function get_date_on_sale_to();

    public function get_total_sales();

    public function get_tax_status();

    public function get_tax_class();

    public function get_manage_stock();

    public function get_stock_quantity();

    public function get_stock_status();

    public function get_backorders();

    public function get_sold_individually();

    public function get_weight();

    public function get_length();

    public function get_width();

    public function get_height();

    public function get_upsell_ids();

    public function get_cross_sell_ids();

    public function get_parent_id();

    public function get_reviews_allowed();

    public function get_purchase_note();

    public function get_attributes();

    public function get_default_attributes();

    public function get_menu_order();

    public function get_virtual();

    public function get_downloadable();

    public function get_category_ids();

    public function get_tag_ids();

    public function get_shipping_class_id();

    public function get_downloads();

    public function get_image_id();

    public function get_gallery_image_ids();

    public function get_download_limit();

    public function get_download_expiry();

    public function get_rating_counts();

    public function get_average_rating();

    public function get_review_count();

    public function get_id();

    public function get_title();

    #seters:

    public function set_props($props);

    public function set_name($value);

    public function set_slug($value);

    public function set_date_created($value);

    public function set_date_modified($value);

    public function set_status($value);

    public function set_featured($value);

    public function set_catalog_visibility($value);

    public function set_description($value);

    public function set_short_description($value);

    public function set_sku($value);

    public function set_price($value);

    public function set_regular_price($value);

    public function set_sale_price($value);

    public function set_date_on_sale_from($value);

    public function set_date_on_sale_to($value);

    public function set_total_sales($value);

    public function set_tax_status($value);

    public function set_tax_class($value);

    public function set_manage_stock($value);

    public function set_stock_quantity($value);

    public function set_stock_status($value);

    public function set_backorders($value);

    public function set_sold_individually($value);

    public function set_weight($value);

    public function set_length($value);

    public function set_width($value);

    public function set_height($value);

    public function set_upsell_ids($value);

    public function set_cross_sell_ids($value);

    public function set_parent_id($value);

    public function set_reviews_allowed($value);

    public function set_purchase_note($value);

    public function set_attributes($value);

    public function set_default_attributes($value);

    public function set_menu_order($value);

    public function set_virtual($value);

    public function set_downloadable($value);

    public function set_category_ids($value);

    public function set_tag_ids($value);

    public function set_shipping_class_id($value);

    public function set_downloads($value);

    public function set_image_id($value);

    public function set_gallery_image_ids($value);

    public function set_download_limit($value);

    public function set_download_expiry($value);

    public function set_rating_counts($value);

    public function set_average_rating($value);

    public function set_review_count($value);

    public function set_id($value);

    public function set_title($value);
}