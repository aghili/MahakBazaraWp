<?php
/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 10/1/2018
 * Time: 9:26 AM
 */
require_once 'mappers/class-customer_id_mapper.php';

class bazara_customer implements \Serializable
{
    public static $class_prfx = 'u';
    private $done;
    private $changed;
    private $people;

    public function __construct($people_bazara = null)
    {
        if ($people_bazara !== null) {
            {
                $this->people = $people_bazara;
                $this->changed = true;
            }//get_base_info($price_bazara);
        }
        //$this->done = false;
    }

    public function set_customer($bazara_customer)
    {
        $this->changed = true;
        return $this->people = $bazara_customer;
    }

    public function get_db_id()
    {
        return $this->class_prfx() . $this->get_id();
    }

    public function class_prfx()
    {
        return bazara_customer::$class_prfx;
    }

    public function get_id()
    {
        return $this->people->PersonID;
    }

    public function get_row_version()
    {
        return $this->people->RowVersion;
    }

    public function get_date_modified()
    {
        return $this->people->UpdateDate;
    }

    public function get_display_name()
    {
        return "$this->people->FirstName $this->people->LastName";
    }

    public function set_done()
    {
        $this->done = true;
    }

    public function is_done()
    {
        return $this->done;
    }

    public function is_changed()
    {
        return $this->changed;
    }

    public function save()
    {
        $result = [];
        try {
            $customer = null;
            $id_customer = customer_id_mapper::get_instance()->get_id($this->get_id());
            if ($id_customer)
                $customer = shop_action::get_customer($id_customer);
            if (is_wp_error($customer))
                return;
            if ($customer instanceof shop_customer) {
                $changes = $this->get_changes();
                $result['changes'] = $changes;
                $data = [
                    'customer' => $changes
                ];
                if (!is_null($customer)) {
                    $result['exist'] = true;
                    if ($this->is_deleted() === true) {
                        $result['most_delete'] = true;
                        $customer->remove();
                        //$customer->set_status(server_customer_status::trash);
                    }
//                else {
//                    $result['most_update'] = true;
//                    $customer->set_props($changes);
//                }

                    //update_post_meta($id, '_customer_attributes', $attributes);
//		}
                    try {
                        $result['apply_changes'] = true;
                    } catch (Exception $ex) {
                        $result['apply_changes'] = "Cant apply changes \nReason:$ex->getMessage()";
                    }

                } else {
                    $result['exist'] = false;
                    try {
                        //$data['customer']['title'] = $data['customer']['name'];
                        $customer = shop_action::create_customer($this);
                        $result['customer'] = $customer;
                        if ($customer instanceof WP_Error)
                            throw new Exception($customer->get_error_message());
                        $customer->save();
                        $result['create_customer'] = true;
                        $result['add_to_ids'] = customer_id_mapper::get_instance()->add($customer->get_id(), $this->get_id());
                    } catch (Exception $ex) {
                        $result['create_customer'] = "Cant save customer \nReason:$ex->getMessage()";
                        return;
                    }
                }
                $result['update_fields'] = $customer->update_by_bazara($this);
                try {
                    //$customer->apply_changes();
                    $customer->save();
                    $result['save_customer'] = true;
                } catch (Exception $ex) {
                    $result['save_customer'] = "Cant save customer \nReason:$ex->getMessage()";
                }
            }
        } finally {
            //do_action('woocommerce_api_edit_customer', $id_customer, $data);

            return ['result' => $result, 'return' => $customer];
        }
    }

    public function get_changes()
    {
        $temp = [];

        $temp['email'] = $this->get_email();
        $temp['first_name'] = $this->get_first_name();
        $temp['last_name'] = $this->get_last_name();
        $temp['display_name'] = $this->get_last_name();

        return $temp;
    }

    public function get_email()
    {
        return $this->people->Email;
    }

    public function get_first_name()
    {
        return $this->people->FirstName;
    }

    public function get_last_name()
    {
        return $this->people->LastName;
    }

//    private function create_customer($request){
//        try {
//            if ( ! empty( $request['id'] ) ) {
//                throw new WC_REST_Exception( 'woocommerce_rest_customer_exists', __( 'Cannot create existing resource.', 'woocommerce' ), 400 );
//            }
//
//            // Sets the username.
//            $request['username'] = ! empty( $request['username'] ) ? $request['username'] : '';
//
//            // Sets the password.
//            $request['password'] = ! empty( $request['password'] ) ? $request['password'] : '';
//
//            // Create customer.
//            $customer = new WC_Customer;
//            $customer->set_username( $request['username'] );
//            $customer->set_password( $request['password'] );
//            $customer->set_email( $request['email'] );
//            $this->update_customer_meta_fields( $customer, $request );
//            $customer->save();
//
//            if ( ! $customer->get_id() ) {
//                throw new WC_REST_Exception( 'woocommerce_rest_cannot_create', __( 'This resource cannot be created.', 'woocommerce' ), 400 );
//            }
//
//            $user_data = get_userdata( $customer->get_id() );
//            $this->update_additional_fields_for_object( $user_data, $request );
//
//            /**
//             * Fires after a customer is created or updated via the REST API.
//             *
//             * @param WP_User         $user_data Data used to create the customer.
//             * @param WP_REST_Request $request   Request object.
//             * @param boolean         $creating  True when creating customer, false when updating customer.
//             */
//            do_action( 'woocommerce_rest_insert_customer', $user_data, $request, true );
//
//            $request->set_param( 'context', 'edit' );
//            $response = $this->prepare_item_for_response( $user_data, $request );
//            $response = rest_ensure_response( $response );
//            $response->set_status( 201 );
//            $response->header( 'Location', rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $customer->get_id() ) ) );
//
//            return $response;
//        } catch ( Exception $e ) {
//            return new WP_Error( $e->getErrorCode(), $e->getMessage(), array( 'status' => $e->getCode() ) );
//        }
//    }

    public function is_deleted()
    {
        return $this->people->Deleted;
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
        return (serialize($this->people));
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
        $this->people
            = unserialize($serialized);
    }
}