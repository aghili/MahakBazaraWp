<?php

/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 9/27/2018
 * Time: 11:55 AM
 */
class sync_list
{
    //private static $sync_list = null;

    private static $instance;

    private function __construct()
    {
        try {
            //$this->load();
        } catch (Exception $ex) {
            //error
        }
    }

    public static function  &get_instance()
    {
        if (sync_list::$instance == null) {
            sync_list::$instance = new self();
        }
        return sync_list::$instance;
    }

//    private function load()
//    {
//        global $wpdb;
//        //echo "load list\n";
//        $this->sync_list = [];
//
//        $result = $wpdb->get_results("SELECT * FROM $this->tablename");
//        if(!$result)
//            throw new Exception('cant get query from table '.$this->tablename);
//        foreach($result as $record){
//            //var_dump($record->value);
//            $bazara_product = unserialize($record->value);
//            $this->sync_list[$bazara_product->get_db_id()] =$bazara_product;
//        }
//        //echo "load done\n";
//        //var_dump($this->sync_list);
//    }

//    public function save(){
//        $result=[];
//        $this->get_sync_list();
//        foreach($this->sync_list as $product)
//        if(!is_null($product)&&$product->is_changed())
//            $result['items'][$product->get_db_id()]=$this->save_item($product);
//        $result['done']=true;
//        return $result;
//        //update_option('wpmbt_sync_list',serialize($this->sync_list),true);
//    }

//    public function &get_sync_list()
//    {
//        $most_remove = [];
//        foreach ($this->sync_list as $key => $item)
//        if(!is_null($item))
//        {
//               if($item->is_done()===true) {
//                $most_remove[] = $key;
//            }
//        }
//        foreach ($most_remove as $index)
//            $this->remove($this->sync_list[$index]);
//        return $this->sync_list;
//
//    }

    public function remove($item)
    {
        global $wpdb;
        $wpdb->delete(DB_NAME_SYNC_LIST, ['id' => $item->get_db_id()]);
//        unset($this->sync_list[$product->get_db_id()]);

    }

    public function count()
    {
        global $wpdb;
        $result = $wpdb->get_row('SELECT count(*) as count FROM ' . DB_NAME_SYNC_LIST);
        if (!$result)
            throw new Exception ("Error on get count \nReason :$wpdb->last_error\nquery : $wpdb->last_query");
        return $result->count;
    }

    public function count_products()
    {
        global $wpdb;
        $result = $wpdb->get_row('SELECT count(*) as count FROM ' . DB_NAME_SYNC_LIST . " WHERE id like 'p%'");
        if (!$result)
            throw new Exception ("Error on get count \nReason :$wpdb->last_error\nquery : $wpdb->last_query");
        return $result->count;
    }

    public function count_customers()
    {
        global $wpdb;
        $result = $wpdb->get_row('SELECT count(*) as count FROM ' . DB_NAME_SYNC_LIST . " WHERE id like 'u%'");
        if (!$result)
            throw new Exception ("Error on get count \nReason :$wpdb->last_error\nquery : $wpdb->last_query");
        return $result->count;
    }

    public function add($item)
    {
        $result = [
            'exist' => true,
        ];
//        $result['exist'] = isset($this->sync_list[$item->class_prfx().$item->get_db_id()]);
//        if ($result['exist']==false)
        {
            global $wpdb;
            $result['insert'] = $wpdb->insert(DB_NAME_SYNC_LIST, ['id' => $item->get_db_id(), 'value' => serialize($item)]);
            $result['error'] = $wpdb->last_error;
            $result['sql'] = $wpdb->last_query;
        }
//        $this->sync_list[$item->get_db_id()] = $item;
//        $result['sync_list']= $this->sync_list;
        return $result;
    }

    public function get_product($product_id)
    {
        return $this->get(bazara_product::$class_prfx, $product_id);
    }

    public function get($type = '', $id = '')
    {
        global $wpdb;
        $sql = 'SELECT value FROM ' . DB_NAME_SYNC_LIST;
        if (($type !== '') || ($id !== ''))
            $sql .= " WHERE id = \"{$type}{$id}\" ";
        else
            $sql .= " ORDER BY RAND() LIMIT 1;";
        $result = $wpdb->get_row($sql);
        if (!$result)
            return null;
        //foreach($result as $record)
        {
            //var_dump($record->value);
            return unserialize($result->value);
            //$this->sync_list[$bazara_product->get_db_id()] =$bazara_product;
        }
    }

    public function &get_customer($customer_id)
    {
        return $this->get(bazara_customer::$class_prfx, $customer_id);
    }

    public function save_item($item)
    {
        $result = [
        ];
        global $wpdb;
        $result['update'] = $wpdb->update(DB_NAME_SYNC_LIST, ['value' => serialize($item)], ['id' => $item->get_db_id()]);
        $result['error'] = $wpdb->last_error;
        $result['sql'] = $wpdb->last_query;
        return $result;
    }
}