<?php

/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 7/14/2018
 * Time: 2:52 PM
 */

if (!class_exists(bazara_product::class))
    require_once("adapters/bazara/class-bazara_product.php");
if (!class_exists(bazara_customer::class))
    require_once("adapters/bazara/class-bazara_customer.php");
if (!class_exists(sync_server::class))
    require_once("communication/class-sync-server.php");
if (!class_exists(sync_list::class))
    require_once("data/class-synclist.php");


class sync
{
    private static $instance = null;

    private $options;

    private $sync_list;//= new sync_list();

    //private $id_mapper ;//= new product_id_mapper();
    private $token;

    private function __construct()
    {
        $this->options = wpmbt_options::GetInstance();
        $this->sync_list = sync_list::get_instance();
        //$this->id_mapper =product_id_mapper::get_instance();
    }

    public static function  &GetInstance()
    {
        if (sync::$instance == null)
            sync::$instance = new sync();
        return sync::$instance;
    }

    public function need_sync()
    {
        $log = log::get_instance();
        try {
            $log_row = $log->get('sync', 1, 1)[0];
            if (!$log_row)
                return false;
            if ($log_row instanceof log_sync_item) {
                $def = time() - $log_row->get_start_time();

                $period = $this->options->options['sync_period_time'];
                if ($period < 1)
                    $period = 1;
                $period *= 60;
                return $def > $period;
            }
        } catch (Exception $ex) {
        }
        return false;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function get_sync($hit_from)
    {
        $sync_log = new log_sync_item();
        switch ($hit_from) {
            case 'user':
                $sync_log->set_synced_by_user();
                break;
            case 'website':
                $sync_log->set_synced_by_website();
                break;
            case 'server':
                $sync_log->set_synced_by_server();
                break;
        }
        $sync_log->set_start();
        $sync_log->save();
        $result = [
            'sync_enable' => $this->options->options['first_conf_done'],
            'last_sync_time' => $this->options->options['last_sync_time']
        ];
        if ($this->options->options['first_conf_done'] === false)
            return false;

        $sync_server = sync_server::GetInstance();

        try {
            if (!$response = $sync_server->login($this->options->options['bz_user_id'], $this->options->options['bz_pass_code']))
                throw new Exception("Cant login to server");

            if (!isset($response->token))
                throw new Exception("token isnt valid");

            $this->options->options['token'] = $response->token;
        } catch (Exception $ex) {
            throw new Exception("Cant login to server .\nReason : $ex->getMessage()", 2001);
        }
        try {
            $result['get_products'] = $this->get_sync_products();
            $sync_log->set_count_product(count($result['get_products']['products']));
            $sync_log->save();
        } catch (Exception $ex) {
            throw new Exception("cant sync product list with server .\nReason : $ex->getMessage()", 2001);
        }

        try {
            $result['get_prices'] = $this->get_sync_prices();
        } catch (Exception $ex) {
            throw new Exception("cant sync price list with server .\nReason : $ex->getMessage()", 2001);
        }

        try {
            $result['get_images'] = $this->get_sync_images();
        } catch (Exception $ex) {
            throw new Exception("cant sync image list with server .\nReason : $ex->getMessage()", 2001);
        }

        try {
            $result['get_customers'] = $this->get_sync_customers();
            $sync_log->set_count_customer(count($result['get_customers']['customers']));
            $sync_log->save();
        } catch (Exception $ex) {
            throw new Exception("cant sync customer list with server .\nReason : $ex->getMessage()", 2001);
        }

        $this->ResetSyncTime();
        $result['current_sync_time'] = $this->options->options['last_sync_time'];
        $sync_log->set_end();
        $sync_log->save();
        return $result;
    }

    private function get_sync_products()
    {
        $result = [];
        $response = false;
        $sync_server = sync_server::GetInstance();
        try {
            $response = $sync_server->get_products();
            $result['sync_with_server'] = true;
        } catch (Exception $ex) {
            throw new Exception ("cant get data from bazara server Reason: $ex->getMessage()", 2002);
        }
        //$result['sync_list']=$this->sync_list->get_sync_list();
        if ($response !== false) {
            $result['sync_correct'] = true;
            foreach ($response as $item) try {
                $result_product = [
                    'ProductID' => $item->ProductID,
                ];
                try {
                    $product = $this->sync_list->get_product($item->ProductID);
                    $result_product['product_action']['get_product'] = true;
                } catch (Exception $ex) {
                    $result_product['product_action']['get_product'] = $ex->getMessage();
                    continue;
                }
                if (!$product) {
                    $result_product['product_action'] = [
                        'status' => 'not_exist_in_list',
                        'create' => false,
                        'add_list' => false,
                        'done' => false
                    ];
                    try {
                        $product = new bazara_product($item);
                        $result_product['product_action']['create'] = true;
                    } catch (Exception $ex) {
                        $result_product['product_action']['create']
                            = $ex->getMessage();
                        continue;
                    }
                    try {
                        //$result_product['product_action']['add_list']=
                        $this->sync_list->add($product);
                    } catch (Exception $ex) {
                        $result_product['product_action']['add_list']
                            = $ex->getMessage();
                        continue;
                    }
                    $result_product['product_action']['done'] = true;
                } else {
                    //$result_product['product']= $product;
                    $result_product['product_action'] = [
                        'status' => 'exist_in_list',
                    ];
                    try {
                        $product->update_by_bazara_product($item);
                        //$result_product['product_action']['update_list'] =
                        $this->sync_list->save_item($product);
                        $result_product['product_action']['update'] = true;
                    } catch (Exception $ex) {
                        $result_product['product_action']['update'] = "Update bazara product item error \nReason: $ex->getMessage()";
                        continue;
                    }
                    $result_product['product_action']['done'] = true;
                }
            } finally {
                $result['products'][] = $result_product;
            }
        } else
            $result['sync_correct'] = false;
        try {
            //$result['save_sync_list'] =
            $this->save_sync_list();
        } catch (Exception $ex) {
            $result['save_sync_list'] = "Save sync list error \nReason:$ex->getMessage()";
        }
        return $result;
    }

    private function save_sync_list()
    {
        //return $this->sync_list->save();
    }

    private function get_sync_prices()
    {
        $response = false;
        $result = [];
        $sync_server = sync_server::GetInstance();
        try {
            $response = $sync_server->get_prices();
            $result['sync_with_server'] = true;
        } catch (Exception $ex) {
            throw new Exception ("cant get data from bazara server Reason: $ex->getMessage()", 2002);
        }
        if ($response !== false) {
            $result['sync_correct'] = true;
            foreach ($response as $item) try {
                $result_product = [
                    'ProductID' => $item->ProductID,
                ];
                $product = null;
                try {
                    $product = $this->sync_list->get_product($item->ProductID);
                    $result_product['product_action']['get_product'] = true;
                } catch (Exception $ex) {
                    $result_product['product_action']['get_product'] = $ex->getMessage();
                    continue;
                }
                if (!$product) {
                    $result_product['product_action'] = [
                        'status' => 'not_exist_in_list',
                        'create' => false,
                        'add_list' => false,
                        'done' => false
                    ];
                    try {
                        $product = new bazara_product();
                        $product->set_id($item->ProductID);
                        $result_product['product_action']['create'] = true;
                    } catch (Exception $ex) {
                        $result_product['product_action']['create'] = $ex->getMessage();
                        continue;
                    }
                    try {

                        $result_product['product_action']['add_list'] = $this->sync_list->add($product);
                    } catch (Exception $ex) {
                        $result_product['product_action']['add_list'] = $ex->getMessage();
                        continue;
                    }
                } else {
                    $result_product['product_action'] = [
                        'status' => 'exist_in_list',
                    ];
                }
                try {
                    $product->set_price_bazara($item);
                    //$result_product['product_action']['update_list'] =
                    $this->sync_list->save_item($product);
                    $result_product['product_action']['update_price'] = true;
                } catch (Exception $ex) {
                    $result_product['product_action']['update_price'] = $ex->getMessage();
                    continue;
                }
                $result_product['product_action']['done'] = true;
            } finally {
                $result['prices'][] = $result_product;
            }
        } else
            $result['sync_correct'] = false;
        try {
            //$result['save_sync_list'] =
            $this->save_sync_list();;
        } catch (Exception $ex) {
            $result['save_sync_list'] = "Save sync list error \nReason:$ex->getMessage()";
        }
        return $result;
    }


    private function get_sync_images()
    {
        $response = false;
        $sync_server = sync_server::GetInstance();
        try {
            $response = $sync_server->get_images();
            $result['sync_with_server'] = true;
        } catch (Exception $ex) {
            throw new Exception ("cant get data from bazara server Reason: $ex->getMessage()", 2002);
        }
        if ($response !== false) {
            $result['sync_correct'] = true;
            foreach ($response as $item) try {
                $result_product = [
                    'ProductID' => $item->ProductID,
                ];
                try {
                    $product = $this->sync_list->get_product($item->ProductID);
                    $result_product['product_action']['get_product'] = true;
                } catch (Exception $ex) {
                    $result_product['product_action']['get_product'] = $ex->getMessage();
                    continue;
                }
                if (!$product) {
                    $result_product['product_action'] = [
                        'status' => 'not_exist_in_list',
                        'create' => false,
                        'add_list' => false,
                        'done' => false
                    ];
                    try {
                        $product = new bazara_product();
                        $product->set_id($item->ProductID);
                        $result_product['product_action']['create'] = true;
                    } catch (Exception $ex) {
                        $result_product['product_action']['create'] = $ex->getMessage();
                        continue;
                    }
                    try {
                        $result_product['product_action']['add_list'] = $this->sync_list->add($product);
                    } catch (Exception $ex) {
                        $result_product['product_action']['add_list'] = $ex->getMessage();
                        continue;
                    }
                } else {
                    $result_product['product_action'] = [
                        'status' => 'exist_in_list',
                    ];
                }
                try {
                    $product->set_image_bazara($item);
                    $result_product['product_action']['update_list'] = $this->sync_list->save_item($product);
                    $result_product['product_action']['update_image'] = true;
                } catch (Exception $ex) {
                    $result_product['product_action']['update_image'] = $ex->getMessage();
                    continue;
                }
                $result_product['product_action']['done'] = true;
            } finally {
                $result['images'][] = $result_product;
            }
        } else
            $result['sync_correct'] = false;
        try {
            $result['save_sync_list'] = $this->save_sync_list();;
        } catch (Exception $ex) {
            $result['save_sync_list'] = "Save sync list error \nReason:$ex->getMessage()";
        }
        return $result;
    }


    private function get_sync_customers()
    {
        $response = false;
        $sync_server = sync_server::GetInstance();
        try {
            $response = $sync_server->get_customers();
            $result['sync_with_server'] = true;
        } catch (Exception $ex) {
            throw new Exception ("cant get data from bazara server Reason: $ex->getMessage()", 2002);
        }
        if ($response !== false) {
            $result['sync_correct'] = true;
            foreach ($response as $item) try {
                $result_customer = [
                    'PersonID' => $item->PersonID,
                ];
                try {
                    $customer = $this->sync_list->get_customer($item->PersonID);
                    $result_customer['customer_action']['get_customer'] = true;
                } catch (Exception $ex) {
                    $result_customer['customer_action']['get_customer'] = $ex->getMessage();
                    continue;
                }
                if (!$customer) {
                    $result_customer['customer_action'] = [
                        'status' => 'not_exist_in_list',
                        'create' => false,
                        'add_list' => false,
                        'done' => false
                    ];
                    try {
                        $customer = new bazara_customer($item);
                        $result_customer['customer_action']['create'] = true;
                    } catch (Exception $ex) {
                        $result_customer['customer_action']['create'] = $ex->getMessage();
                        continue;
                    }
                    try {
                        $result_customer['customer_action']['add_list'] = $this->sync_list->add($customer);
                    } catch (Exception $ex) {
                        $result_customer['customer_action']['add_list'] = $ex->getMessage();
                        continue;
                    }
                } else {
                    $result_customer['customer_action'] = [
                        'status' => 'exist_in_list',
                    ];
                }
                try {
                    $customer->set_customer($item);
                    $result_customer['customer_action']['update_list'] = $this->sync_list->save_item($customer);

                    $result_customer['customer_action']['update_data'] = true;
                } catch (Exception $ex) {
                    $result_customer['customer_action']['update_data'] = $ex->getMessage();
                    continue;
                }
                $result_customer['customer_action']['done'] = true;
            } finally {
                $result['customers'][] = $result_customer;
            }
        } else
            $result['sync_correct'] = false;
        try {
            $result['save_sync_list'] = $this->save_sync_list();;
        } catch (Exception $ex) {
            $result['save_sync_list'] = "Save sync list error \nReason:$ex->getMessage()";
        }
        return $result;
    }

    private function ResetSyncTime()
    {
        $this->options->options['last_sync_time'] = time();
        $this->options->save();
    }

    public function update_records()
    {
        $result = [];
        try {
            if ($this->has_record_for_update()) {
                //echo "has_record_for_update\n";
                $sync_count = $this->options->options['update_good_per_hint'];
                while ($item = $this->sync_list->get()) {
                    $result_row =
                        [
                            'status' => 'success',
                            'row_version' => $item->get_row_version(),
                            'row_update_date' => $item->get_date_modified(),
                        ];
                    try {
                        $ret = $item->save();
                        $result_row['update'] = $ret['result'];
                        //var_dump($product_bazara);
                        //var_dump($result_row);
                        $product = $ret['return'];
                        //if (!is_a($product, 'WP_Error'))
                        //$result_row['wp_id'] = $product->get_id();
                        $result_row['result'] = $item;
                        $this->sync_list->remove($item);
                    } catch (Exception $ex) {
                        $result_row['status'] = 'error';
                        $result_row['message'] = "[ERROR] $ex->getCode() : $ex->getMessage()";
                    }
                    $result[$item->get_db_id()] = $result_row;
                    $this->save_sync_list();
                    $sync_count--;
                    if ($sync_count == 0)
                        break;
                }
            }
        } finally {

        }
        return $result;
//        foreach($this->plg_options as $product) {
//            $this->plg_options['sync_list'][$product->ProductID];
//        }
    }
//
//    private function update_record(&$product_bazara)
//    {
//        //echo "Start update record\n";
//        $result = [];
//        try {
//            $product = null;
////            if ($id_product = $this->id_mapper->get_product_id($product_bazara->get_id()))
////                $product = wc_get_product($id_product);
////            $changes = $product_bazara->get_changes();
////            $result['changes'] = $changes;
////            $data = [
////                'product' => $changes
////            ];
////            if ($product != false) {
////                $result['exist'] = true;
////                if ($product_bazara->is_deleted() === true) {
////                    $result['most_delete'] = true;
////                    $product->delete();
////                    //$product->set_status(server_product_status::trash);
////                } else {
////                    $result['most_update'] = true;
////                    $product->set_props($changes);
////                }
////
////                //update_post_meta($id, '_product_attributes', $attributes);
//////		}
////                try {
////                    $result['apply_changes'] = true;
////                } catch (Exception $ex) {
////                    $result['apply_changes'] = "Cant apply changes \nReason:$ex->getMessage()";
////                }
////
////            } else {
////                $result['exist'] = false;
////                try {
////                    $data['product']['title'] = $data['product']['name'];
////                    $product = wc_product_act::create_product($data);
////                    $result['create_product'] = true;
////                } catch (Exception $ex) {
////                    $result['create_product'] = "Cant save product \nReason:$ex->getMessage()";
////                }
////                if (!is_a($product, 'WP_Error')) {
////                    $this->id_mapper->add($product->get_id(), $product_bazara->get_id());
////                } else {
////                    $result['create_product'] = $product;
////                    return;
////                }
////            }
////
////            foreach ($changes as $key => $change)
////                try {
////                    $result['update_product'][$key]['value'] = $change;
////                    $product->set_manage_stock(true);
////                    $product->{"set_" . $key}("$change");
////                    $result['update_product'][$key]['done'] = true;
////                } catch (Exception $ex) {
////                    $result['update_product'][$key]['value'] = "cant set '$change' for $key \nReason : $ex->getMessage()";
////                }
////            try {
////                //$product->apply_changes();
////                $product->save();
////                $result['save_product'] = true;
////            } catch (Exception $ex) {
////                $result['save_product'] = "Cant save product \nReason:$ex->getMessage()";
////            }
//        } finally {
//            //do_action('woocommerce_api_edit_product', $id_product, $data);
//
//            return ['result' => $result, 'return' => $product];
//        }
//    }

    public function has_record_for_update()
    {
        return $this->sync_list->count() > 0;
    }

    public function get_update_info()
    {
        $result = [];
        $result['count_products'] = $this->sync_list->count_products();
        $result['count_customers'] = $this->sync_list->count_customers();
        return $result;
    }
}