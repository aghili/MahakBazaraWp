<?php
/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 9/29/2018
 * Time: 4:00 PM
 */

if (!class_exists(int_shop_adapter::class))
    require_once("interface/int_shop_adapter.php");
if (!class_exists(int_shop_customer::class))
    require_once("interface/int_shop_customer.php");
if (!class_exists(int_shop_product::class))
    require_once("interface/int_shop_product.php");

class shop_action implements int_shop_adapter
{
    public static function get_product($id_product)
    {
        return new shop_product(wc_get_product($id_product));
    }

    public static function create_product($data)
    {
        return new shop_product($data);
    }

    public static function clear_product($product_id)
    {
        if (!is_numeric($product_id) || 0 >= $product_id) {
            return;
        }

        // Delete product attachments
        $attachments = get_children(array(
            'post_parent' => $product_id,
            'post_status' => 'any',
            'post_type' => 'attachment',
        ));

        foreach ((array)$attachments as $attachment) {
            wp_delete_attachment($attachment->ID, true);
        }

        // Delete product
        $product = wc_get_product($product_id);
        $product->delete();
    }

    public static function get_customer($id_customer)
    {
        return new shop_customer(new WC_Customer($id_customer));
    }

    public static function create_customer($bazara_customer)
    {
        return new shop_customer($bazara_customer);
    }

    public static function clear_customer($id_customer)
    {
        if (!is_numeric($id_customer) || 0 >= $id_customer) {
            return;
        }

        // Delete customer
        $customer = new WC_Customer($id_customer);
        $customer->delete();
    }

}

class shop_product implements int_shop_product
{

    private $product;

    private  function  get_product($id){
        return wc_get_product($id);
    }

    private function create_product($data)
    {
        $id = 0;

        try {
            if ( ! isset( $data['product'] ) ) {
                throw new WC_API_Exception( 'woocommerce_api_missing_product_data', sprintf( __( 'No %1$s data specified to create %1$s', 'woocommerce' ), 'product' ), 400 );
            }

            $data = $data['product'];

//            // Check permissions.
//            if ( ! current_user_can( 'publish_products' ) ) {
//                throw new WC_API_Exception( 'woocommerce_api_user_cannot_create_product', __( 'You do not have permission to create products', 'woocommerce' ), 401 );
//            }

            $data = apply_filters( 'woocommerce_api_create_product_data', $data, $this );

            // Check if product title is specified.
            if ( ! isset( $data['title'] ) ) {
                throw new WC_API_Exception( 'woocommerce_api_missing_product_title', sprintf( __( 'Missing parameter %s', 'woocommerce' ), 'title' ), 400 );
            }

            // Check product type.
            if ( ! isset( $data['type'] ) ) {
                $data['type'] = 'simple';
            }

            // Set visible visibility when not sent.
            if ( ! isset( $data['catalog_visibility'] ) ) {
                $data['catalog_visibility'] = 'visible';
            }

            // Validate the product type.
            if ( ! in_array( wc_clean( $data['type'] ), array_keys( wc_get_product_types() ) ) ) {
                throw new WC_API_Exception( 'woocommerce_api_invalid_product_type', sprintf( __( 'Invalid product type - the product type must be any of these: %s', 'woocommerce' ), implode( ', ', array_keys( wc_get_product_types() ) ) ), 400 );
            }

            // Enable description html tags.
            $post_content = isset( $data['description'] ) ? wc_clean( $data['description'] ) : '';
            if ( $post_content && isset( $data['enable_html_description'] ) && true === $data['enable_html_description'] ) {

                $post_content = $data['description'];
            }

            // Enable short description html tags.
            $post_excerpt = isset( $data['short_description'] ) ? wc_clean( $data['short_description'] ) : '';
            if ( $post_excerpt && isset( $data['enable_html_short_description'] ) && true === $data['enable_html_short_description'] ) {
                $post_excerpt = $data['short_description'];
            }

            $classname = WC_Product_Factory::get_classname_from_product_type( $data['type'] );
            if ( ! class_exists( $classname ) ) {
                $classname = 'WC_Product_Simple';
            }
            $product = new $classname();

            $product->set_name( wc_clean( $data['title'] ) );
            $product->set_status( isset( $data['status'] ) ? wc_clean( $data['status'] ) : 'publish' );
            $product->set_short_description( isset( $data['short_description'] ) ? $post_excerpt : '' );
            $product->set_description( isset( $data['description'] ) ? $post_content : '' );
            $product->set_menu_order( isset( $data['menu_order'] ) ? intval( $data['menu_order'] ) : 0 );

            if ( ! empty( $data['name'] ) ) {
                $product->set_slug( sanitize_title( $data['name'] ) );
            }

            // Attempts to create the new product.
            $product->save();
            $id = $product->get_id();

            // Checks for an error in the product creation.
            if ( 0 >= $id ) {
                throw new WC_API_Exception( 'woocommerce_api_cannot_create_product', $id->get_error_message(), 400 );
            }
//            // Check for featured/gallery images, upload it and set it.
//            if ( isset( $data['images'] ) ) {
//                //$product = $this->save_product_images( $product, $data['images'] );
//            }
//
//            // Save product meta fields.
//            $product = $this->save_product_meta( $product, $data );
//            $product->save();

//            // Save variations.
//            if ( isset( $data['type'] ) && 'variable' == $data['type'] && isset( $data['variations'] ) && is_array( $data['variations'] ) ) {
//                $this->save_variations( $product, $data );
//            }

            do_action( 'woocommerce_api_create_product', $id, $data );

            // Clear cache/transients.
            wc_delete_product_transients( $id );

            //$this->server->send_status( 201 );

            return $this->get_product( $id );
        } catch ( WC_Data_Exception $e ) {
            $this->clear_product( $id );
            return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
        } catch ( WC_API_Exception $e ) {
            $this->clear_product( $id );
            return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
        }

    }

    public function __construct($item)
    {
        $temp = [];
        if ($item instanceof WC_Product) {
            $this->product = $item;
            $item->set_status('publish');
        } else if ($item instanceof bazara_product) {
            if ($item->has_record()) {

                $temp['name'] = $item->get_title();
                $temp['catalog_visibility'] = 'visible';
                $temp['description'] = $item->get_description();
                $temp['short_description'] = $item->get_short_description();
                $temp['date_created'] = $item->get_date_create();
                $temp['date_modified'] = $item->get_date_modified();
                //todo:// Add meta fields

            }
            if ($item->has_image()) {
                $temp['images'] = $item->get_images();

            }
            $price = $item->get_price();
            if ($price instanceof bazara_price) {
                $temp['stock_quantity'] = $price->get_stock_quantity();
                $temp['price'] = $price->get_regular_price();
                $temp['regular_price'] = $price->get_regular_price();
                $temp['sale_price'] = $price->get_sale_price();
            }
//            $data = [
//                'product' => $temp
//            ];

            //todo: WC_API_Products::create_product
            $this->product = new WC_Product($temp);
            $this->product->set_status('publish');
            $this->set_by_bazara($item);
        } else
            throw new Exception ("input parameter is not valid");
    }

    public function set_by_bazara($bazara_product)
    {
        $result = [];
        $this->product->set_manage_stock(true);
        $this->product->set_name($bazara_product->get_title());
        foreach ($bazara_product->get_changes() as $key => $change)
            try {
                $result['update_product'][$key]['value'] = $change;
                $this->product->{"set_" . $key}("$change");
                $result['update_product'][$key]['done'] = true;
            } catch (Exception $ex) {
                $result['update_product'][$key]['value'] = "cant set '$change' for $key \nReason : $ex->getMessage()";
            }
        return $result;
    }

    public function save()
    {
        $this->product->save();
    }

    public function remove()
    {
        shop_action::clear_product($this->get_id());
    }

    public function get_id()
    {
        return $this->product->get_id();
    }

    public function get_name()
    {
        return $this->product->get_name();
    }

    public function get_slug()
    {
        return $this->product->get_slug();
    }

    public function get_date_created()
    {
        return $this->product->get_date_created();
    }

    public function get_date_modified()
    {
        return $this->product->get_date_modified();
    }

    public function get_status()
    {
        return $this->product->get_status();
    }

    public function get_featured()
    {
        return $this->product->get_featured();
    }

    public function get_catalog_visibility()
    {
        return $this->product->get_catalog_visibility();
    }

    public function get_description()
    {
        return $this->product->get_description();
    }

    public function get_short_description()
    {
        return $this->product->get_short_description();
    }

    public function get_sku()
    {
        return $this->product->get_sku();
    }

    public function get_price()
    {
        return $this->product->get_price();
    }

    public function get_regular_price()
    {
        return $this->product->get_regular_price();
    }

    public function get_sale_price()
    {
        return $this->product->get_sale_price();
    }

    public function get_date_on_sale_from()
    {
        return $this->product->get_date_on_sale_from();
    }

    public function get_date_on_sale_to()
    {
        return $this->product->get_date_on_sale_to();
    }

    public function get_total_sales()
    {
        return $this->product->get_total_sales();
    }

    public function get_tax_status()
    {
        return $this->product->get_tax_status();
    }

    public function get_tax_class()
    {
        return $this->product->get_tax_class();
    }

    public function get_manage_stock()
    {
        return $this->product->get_manage_stock();
    }

    public function get_stock_quantity()
    {
        return $this->product->get_stock_quantity();
    }

    public function get_stock_status()
    {
        return $this->product->get_stock_status();
    }

    public function get_backorders()
    {
        return $this->product->get_backorders();
    }

    public function get_sold_individually()
    {
        return $this->product->get_sold_individually();
    }

    public function get_weight()
    {
        return $this->product->get_width();
    }

    public function get_length()
    {
        return $this->product->get_length();
    }

    public function get_width()
    {
        return $this->product->get_width();
    }

    public function get_height()
    {
        return $this->product->get_height();
    }

    public function get_upsell_ids()
    {
        return $this->product->get_upsell_ids();
    }

    public function get_cross_sell_ids()
    {
        return $this->product->get_cross_sell_ids();
    }

    public function get_parent_id()
    {
        return $this->product->get_parent_id();
    }

    public function get_reviews_allowed()
    {
        return $this->product->get_reviews_allowed();
    }

    public function get_purchase_note()
    {
        return $this->product->get_purchase_note();
    }

    public function get_attributes()
    {
        return $this->product->get_attributes();
    }

    public function get_default_attributes()
    {
        return $this->product->get_default_attributes();
    }

    public function get_menu_order()
    {
        return $this->product->get_menu_order();
    }

    public function get_virtual()
    {
        return $this->product->get_virtual();
    }

    public function get_downloadable()
    {
        return $this->product->get_downloadable();
    }

    public function get_category_ids()
    {
        return $this->product->get_category_ids();
    }

    public function get_tag_ids()
    {
        return $this->product->get_tag_ids();
    }

    public function get_shipping_class_id()
    {
        return $this->product->get_shipping_class_id();
    }

    public function get_downloads()
    {
        return $this->product->get_downloads();
    }

    public function get_image_id()
    {
        return $this->product->get_image_id();
    }

    public function get_gallery_image_ids()
    {
        return $this->product->get_gallery_image_ids();
    }

    public function get_download_limit()
    {
        return $this->product->get_download_limit();
    }

    public function get_download_expiry()
    {
        return $this->product->get_download_expiry();
    }

    public function get_rating_counts()
    {
        return $this->product->get_rating_counts();
    }

    public function get_average_rating()
    {
        return $this->product->get_average_rating();
    }

    public function get_review_count()
    {
        return $this->product->get_review_count();
    }

    public function set_props($props)
    {
        return $this->product->set_props($props);
    }

    public function get_title()
    {
        return $this->product->get_title();
    }

    public function set_name($value)
    {
        $this->product->set_name($value);
    }

    public function set_slug($value)
    {
        $this->product->set_slug($value);
    }

    public function set_date_created($value)
    {
        $this->product->set_date_created($value);
    }

    public function set_date_modified($value)
    {
        $this->product->set_date_modified($value);
    }

    public function set_status($value)
    {
        $this->product->set_status($value);
    }

    public function set_featured($value)
    {
        $this->product->set_featured($value);
    }

    public function set_catalog_visibility($value)
    {
        $this->product->set_catalog_visibility($value);
    }

    public function set_description($value)
    {
        $this->product->set_description($value);
    }

    public function set_short_description($value)
    {
        $this->product->set_short_description($value);
    }

    public function set_sku($value)
    {
        $this->product->set_sku($value);
    }

    public function set_price($value)
    {
        $this->product->set_price($value);
    }

    public function set_regular_price($value)
    {
        $this->product->set_regular_price($value);
    }

    public function set_sale_price($value)
    {
        $this->product->set_sale_price($value);
    }

    public function set_date_on_sale_from($value)
    {
        $this->product->set_date_on_sale_from($value);
    }

    public function set_date_on_sale_to($value)
    {
        $this->product->set_date_on_sale_to($value);
    }

    public function set_total_sales($value)
    {
        $this->product->set_total_sales($value);
    }

    public function set_tax_status($value)
    {
        $this->product->set_tax_status($value);
    }

    public function set_tax_class($value)
    {
        $this->product->set_tax_class($value);
    }

    public function set_manage_stock($value)
    {
        $this->product->set_manage_stock($value);
    }

    public function set_stock_quantity($value)
    {
        $this->product->set_stock_quantity($value);
    }

    public function set_stock_status($value)
    {
        $this->product->set_stock_status($value);
    }

    public function set_backorders($value)
    {
        $this->product->set_backorders($value);
    }

    public function set_sold_individually($value)
    {
        $this->product->set_sold_individually($value);
    }

    public function set_weight($value)
    {
        $this->product->set_weight($value);
    }

    public function set_length($value)
    {
        $this->product->set_length($value);
    }

    public function set_width($value)
    {
        $this->product->set_width($value);
    }

    public function set_height($value)
    {
        $this->product->set_height($value);
    }

    public function set_upsell_ids($value)
    {
        $this->product->set_upsell_ids($value);
    }

    public function set_cross_sell_ids($value)
    {
        $this->product->set_cross_sell_ids($value);
    }

    public function set_parent_id($value)
    {
        $this->product->set_parent_id($value);
    }

    public function set_reviews_allowed($value)
    {
        $this->product->set_reviews_allowed($value);
    }

    public function set_purchase_note($value)
    {
        $this->product->set_purchase_note($value);
    }

    public function set_attributes($value)
    {
        $this->product->set_attributes($value);
    }

    public function set_default_attributes($value)
    {
        $this->product->set_default_attributes($value);
    }

    public function set_menu_order($value)
    {
        $this->product->set_menu_order($value);
    }

    public function set_virtual($value)
    {
        $this->product->set_virtual($value);
    }

    public function set_downloadable($value)
    {
        $this->product->set_downloadable($value);
    }

    public function set_category_ids($value)
    {
        $this->product->set_category_ids($value);
    }

    public function set_tag_ids($value)
    {
        $this->product->set_tag_ids($value);
    }

    public function set_shipping_class_id($value)
    {
        $this->product->set_shipping_class_id($value);
    }

    public function set_downloads($value)
    {
        $this->product->set_downloads($value);
    }

    public function set_image_id($value)
    {
        $this->product->set_image_id($value);
    }

    public function set_gallery_image_ids($value)
    {
        $this->product->set_gallery_image_ids($value);
    }

    public function set_download_limit($value)
    {
        $this->product->set_download_limit($value);
    }

    public function set_download_expiry($value)
    {
        $this->product->set_download_expiry($value);
    }

    public function set_rating_counts($value)
    {
        $this->product->set_rating_counts($value);
    }

    public function set_average_rating($value)
    {
        $this->product->set_average_rating($value);
    }

    public function set_review_count($value)
    {
        $this->product->set_review_count($value);
    }

    public function set_id($value)
    {
        $this->product->set_id($value);
    }

    public function set_title($value)
    {
        $this->product->set_name($value);
    }
}

class shop_customer implements int_shop_customer
{

    private $customer;

    public function __construct($item)
    {
        $temp = [];
        if ($item instanceof WC_Customer) {
            $this->customer = $item;
        } else if ($item instanceof bazara_customer) {
            $id_customer = wc_create_new_customer($item->get_email());//todo:,$item->get_username(),$item->get_password());
            $this->customer = new WC_Customer($id_customer);
            $this->set_by_bazara($item);
        } else
            throw new Exception ("input parameter is not valid");
    }

    public function set_by_bazara($bazara_customer)
    {
        $result = [];
        foreach ($bazara_customer->get_changes() as $key => $change)
            try {
                $result['update_customer'][$key]['value'] = $change;
                $this->customer->{"set_" . $key}("$change");
                $result['update_customer'][$key]['done'] = true;
            } catch (Exception $ex) {
                $result['update_customer'][$key]['value'] = "cant set '$change' for $key \nReason : $ex->getMessage()";
            }
        return $result;
    }

    public function save()
    {
        $this->customer->save();
    }

    public function remove()
    {
        shop_action::clear_customer($this->get_email());
    }

    public function get_email()
    {
        return $this->customer->get_email();
    }

    public function get_date_created()
    {
        return $this->customer->get_date_created();
    }

    public function get_date_modified()
    {
        return $this->customer->get_date_modified();
    }

    public function get_first_name()
    {
        return $this->customer->get_first_name();
    }

    public function get_last_name()
    {
        return $this->customer->get_last_name();
    }

    public function get_display_name()
    {
        return $this->customer->get_display_name();
    }

    public function get_role()
    {
        return $this->customer->get_role();
    }

    public function get_username()
    {
        return $this->customer->get_username();
    }

    public function set_date_created($value)
    {
        $this->customer->set_date_created($value);
    }

    public function set_date_modified($value)
    {
        $this->customer->set_date_modified($value);
    }

    public function set_email($value)
    {
        $this->customer->set_email($value);
    }

    public function set_first_name($value)
    {
        $this->customer->set_first_name($value);
    }

    public function set_last_name($value)
    {
        $this->customer->set_last_name($value);
    }

    public function set_display_name($value)
    {
        $this->customer->set_display_name($value);
    }

    public function set_role($value)
    {
        $this->customer->set_role($value);
    }

    public function set_username($value)
    {
        $this->customer->set_username($value);
    }
}