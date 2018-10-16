<?php
/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 10/10/2018
 * Time: 10:24 AM
 */

class log_sync_item implements  JsonSerializable
{
    private $db_row;

    private $type;
    private $count_product;
    private $count_customer;

    private $start_time;
    private $duration;
    private $end_time;
    public function __construct($db_row = null)
    {
        $this->set_synced_by_website();
        if ($db_row) {
            if(is_numeric($db_row))
                $this->read_from_db($db_row);
            else
            $this->set_db_row($db_row);
        }
    }

    public function set_synced_by_website()
    {
        $this->type = 'website';
    }

    private function read_from_db($insert_id)
    {
        global $wpdb;
        $db_row = $wpdb->get_row('SELECT * FROM `' . DB_NAME_LOG . "` WHERE id = " . $insert_id);
        //echo $wpdb->last_query;
        if ($db_row)
            $this->set_db_row($db_row);

    }

    private function set_db_row($db_row)
    {
        $this->db_row = $db_row;
        $this->unserialize_data($db_row->value);
    }

    private function unserialize_data($serialized)
    {
        list(
            $this->count_product,
            $this->count_customer,
            $this->start_time,
            $this->end_time,
            $this->duration,
            $this->type
            ) = unserialize($serialized);
    }

    public function set_synced_by_user()
    {
        $this->type = 'user';
    }

    public function set_synced_by_server()
    {
        $this->type = 'server';
    }

    public function set_start()
    {
        $this->start_time = time();
    }

    public function set_end()
    {
        if (is_null($this->start_time))
            throw new Exception ("sync not set to start , first start sync then end that !");
        $this->end_time = time();
        $this->duration = $this->end_time-$this->start_time ;
   }

    public function save()
    {
        $result = [
        ];
        global $wpdb;
        if (!$this->db_row) {
            $result['insert'] =
                $wpdb->insert(
                    DB_NAME_LOG,
                    [
                        'type' => 'sync',
                        'value' => $this->serialize_data(),
                        'description' => ''
                    ]);
            $this->read_from_db($wpdb->insert_id);
            $result['error'] = $wpdb->last_error;
            $result['sql'] = $wpdb->last_query;
        } else {
            $this->db_row->value = $this->serialize_data();
            $q_arr =[
                'value' => $this->db_row->value,
                'description' => $this->db_row->description
            ];
            $result['update'] = $wpdb->update(DB_NAME_LOG, $q_arr,['id'=>$this->get_id()]);
            $result['error'] = $wpdb->last_error;
            $result['sql'] = $wpdb->last_query;
        }
       return $result;
    }

    private function serialize_data()
    {
       return serialize([
           $this->count_product,
           $this->count_customer,
           $this->start_time,
           $this->end_time,
           $this->duration,
           $this->type
       ]);
    }

    public function get_id()
    {
        return $this->db_row->id;
    }

    public function get_description()
    {
        return $this->db_row->description;
    }

    public function jsonSerialize() {
        return [
            'count_product'=>$this->get_count_product(),
            'count_customer'=>$this->get_count_customer(),
            'start_time'=>$this->get_start_time(),
            'end_time'=>$this->get_end_time(),
            'duration'=>$this->get_duration(),
            'synced_by'=>$this->get_sync_type(),
            'id'=>$this->get_id()
        ];
    }

    public function get_count_product()
    {
        return $this->count_product;
    }

    public function set_count_product($count)
    {
        $this->count_product = $count;
    }

    public function get_count_customer()
    {
        return $this->count_customer;
    }

    public function set_count_customer($count)
    {
        $this->count_customer = $count;
    }

    public function get_start_time()
    {
        return $this->start_time;
    }

    public function get_end_time()
    {
        return $this->end_time;
    }

    public function get_duration()
    {
        return $this->duration;
    }

    public function get_sync_type()
    {
        return $this->type;
    }
}