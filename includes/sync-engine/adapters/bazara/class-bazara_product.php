<?php
/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 9/30/2018
 * Time: 4:18 PM
 */
require_once 'mappers/class-product_id_mapper.php';

if (!class_exists(bazara_price::class))
    require_once("class-bazara_price.php");
if (!class_exists(bazara_images::class))
    require_once("class-bazara_images.php");

class bazara_product implements \Serializable
{

    public static $class_prfx = 'p';
    private $is_change = false;
    private $id;
    private $row_version = null;
    private $update_date = null;
    private $record = null;
    private $price = null;
    private $images = null;
    private $done;

    public function __construct($record_bazara = null)
    {
        if ($record_bazara !== null) {
            $this->set_record($record_bazara);
            $this->is_change = true;
        }
        $this->done = false;
    }

    private function set_record($record_bazara)
    {
        $this->record = $record_bazara;
        $this->id = $record_bazara->ProductID;
        $this->row_version = $record_bazara->RowVersion;
        $this->update_date = $record_bazara->UpdateDate;
    }

    public function is_done()
    {
        return $this->done;
    }

    public function set_done()
    {
        $this->done = true;
    }

    public function set_price_bazara($price)
    {
        $this->price = new bazara_price($price);
        $this->set_base_info($this->price);

        $this->is_change = true;
    }

    public function set_base_info($item)
    {
        $this->id = $item->get_id();
        $this->row_version = $item->get_row_version();
        $this->update_date = $item->get_date_modified();
    }

    public function set_image_bazara($images)
    {
        $this->images = new bazara_images($images);
        todo:
        $this->set_base_info($this->images);

        $this->is_change = true;
    }

    public function get_row_version()
    {
        return $this->row_version;
    }

    public function get_db_id()
    {
        return $this->class_prfx() . $this->id;
    }

    public function class_prfx()
    {
        return bazara_product::$class_prfx;
    }

    public function &get_price()
    {
        return $this->price;
    }

    public function update_by_bazara_product($item)
    {
        $this->set_record($item);
        $this->is_change = true;
    }

//    public function remove_from_db(){
//    }

    //todo: add return value for saving object in woo:

    public function save()
    {
        $result = [];
        $product_ids = product_id_mapper::get_instance();
        if (!$product_ids instanceof product_id_mapper)
            return null;
        try {
            $product = null;
            $id_product = $product_ids->get_id($this->get_id());

            //$result['ids']=product_id_mapper::get_instance()->get_product_mapper_list();
            $result['id_product'] = $id_product;
            if ($id_product)
                $product = shop_action::get_product($id_product);
            if (is_wp_error($product))
                return;
            $changes = $this->get_changes();
            $result['changes'] = $changes;
            if (!is_null($product)) {
                $result['exist'] = true;
                if ($this->is_deleted() === true) {
                    $result['most_delete'] = true;
                    $product->remove();
                    //$product->set_status(server_product_status::trash);
                } else {
                    $result['most_update'] = true;
                    $product->set_props($changes);
                }

                //update_post_meta($id, '_product_attributes', $attributes);
//		}
                try {
                    $result['apply_changes'] = true;
                } catch (Exception $ex) {
                    $result['apply_changes'] = "Cant apply changes \nReason:$ex->getMessage()";
                }

            } else {
                $result['exist'] = false;
                try {
                    $product = shop_action::create_product($this);
                    $product->save();
                    $result['create_product'] = true;
                } catch (Exception $ex) {
                    $result['create_product'] = "Cant save product \nReason:$ex->getMessage()";
                }
                if (!is_a($product, 'WP_Error')) {
                    $product_ids->add($product->get_id(), $this->get_id());
                } else {
                    $result['create_product'] = $product;
                    return;
                }
            }

            $result['update_fields'] = $product->update_by_bazara($this);
            try {
                //$product->apply_changes();
                $product->save();
                $result['save_product'] = true;
            } catch (Exception $ex) {
                $result['save_product'] = "Cant save product \nReason:$ex->getMessage()";
            }
        } finally {
            //do_action('woocommerce_api_edit_product', $id_product, $data);

            return ['result' => $result, 'return' => $product];
        }
    }

    public function get_id()
    {
        return $this->id;
    }

    public function  set_id($id)
    {
        $this->id = $id;
    }

    public function get_changes()
    {
        $temp = [];
        if ($this->has_record()) {

            $temp['name'] = $this->get_title();
            $temp['catalog_visibility'] = 'visible';
            $temp['description'] = $this->get_description();
            $temp['short_description'] = $this->get_short_description();
            $temp['date_created'] = $this->get_date_create();
            $temp['date_modified'] = $this->get_date_modified();
            //todo:// Add meta fields

        }
        if ($this->has_image()) {
            $temp['images'] = $this->get_images();

        }
        if ($this->has_price()) {
            $temp['stock_quantity'] = $this->price->get_stock_quantity();
            $temp['price'] = $this->price->get_regular_price();
            $temp['regular_price'] = $this->price->get_regular_price();
            $temp['sale_price'] = $this->price->get_sale_price();

        }
        return $temp;
    }

    public function has_record()
    {
        return $this->record !== null;
    }

    public function get_title()
    {
        return $this->record->Name;
    }

    public function get_description()
    {
        return $this->record->Comment;
    }

    public function get_short_description()
    {
        return $this->record->Comment;
    }

    public function get_date_create()
    {
        return $this->record->CreateDate;
    }

    public function get_date_modified()
    {
        return $this->update_date;
    }

    public function has_image()
    {
        return $this->image !== null;
    }

    public function get_images()
    {
        //todo:return images in wp format
        return $this->image->get_images();
    }

    public function has_price()
    {
        return $this->price !== null;
    }

    public function is_deleted()
    {
        return $this->record->Deleted;
    }

    public function is_changed()
    {
        return $this->is_change;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        // TODO: Implement serialize() method.
        return serialize([
            $this->id,
            $this->row_version,
            $this->update_date,
            $this->record,
            $this->price,
            $this->images,
            $this->done
        ]);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        // TODO: Implement unserialize() method.
        list(
            $this->id,
            $this->row_version,
            $this->update_date,
            $this->record,
            $this->price,
            $this->images,
            $this->done
            ) = unserialize($serialized);
    }
}