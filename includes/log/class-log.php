<?php
/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 9/27/2018
 * Time: 11:55 AM
 */

if (!class_exists(log_sync_item::class))
    require_once("items/class-log_sync_item.php");

class log
{
    //private static $sync_list = null;

    private static $instance;

    private $tablename;

    private function __construct()
    {
        global $wpdb;
        $this->tablename = DB_NAME_LOG;
        //echo "create sync list\n";
        try {
            //$this->load();
        } catch (Exception $ex) {
            //error
        }
    }

    public static function  &get_instance()
    {
        if (log::$instance == null) {
            log::$instance = new self();
            return log::$instance;
        }
        return log::$instance;
    }

//    public function remove($item){
//        global $wpdb;
//        $wpdb->delete($this->tablename,['id'=>$item->get_db_id()]);
//    }

    public function get($type = 'main', $count = 10, $offset = 0)
    {
        global $wpdb;
        $sql = "SELECT * FROM $this->tablename ";
        $sql .= " WHERE `type` like \"{$type}\" ";
        $sql .= " ORDER BY `id` DESC LIMIT $offset,$count;";
        $result_db = $wpdb->get_results($sql);
        if (!empty($wpdb->last_error))
            return $wpdb->last_error;
        $result = [];
        foreach ($result_db as $key => $record) {
            $class_log = "log_{$type}_item";
            $result[] = new $class_log($record);
        }
        return $result;
    }

    public function count()
    {
        global $wpdb;
        $result = $wpdb->get_row("SELECT count(*) as count FROM $this->tablename ");
        if (!$result)
            throw new Exception ("Error on get count \nReason :$wpdb->last_error\nquery : $wpdb->last_query");
        return $result->count;
    }

    public function count_products()
    {
        global $wpdb;
        $result = $wpdb->get_row("SELECT count(*) as count FROM $this->tablename WHERE id like 'p%'");
        if (!$result)
            throw new Exception ("Error on get count \nReason :$wpdb->last_error\nquery : $wpdb->last_query");
        return $result->count;
    }

    public function count_customers()
    {
        global $wpdb;
        $result = $wpdb->get_row("SELECT count(*) as count FROM $this->tablename WHERE id like 'u%'");
        if (!$result)
            throw new Exception ("Error on get count \nReason :$wpdb->last_error\nquery : $wpdb->last_query");
        return $result->count;
    }

//    public function save_item($item)
//    {
//        $result = [
//        ];
//        global $wpdb;
//        $result['update'] = $wpdb->update($this->tablename, [ 'value' => serialize($item)],['id' => $item->get_db_id()]);
//        $result['error'] = $wpdb->last_error;
//        $result['sql'] = $wpdb->last_query;
//        return $result;
//    }
}