<?php

/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 7/14/2018
 * Time: 3:15 PM
 */
class sync_server
{
    private static $instance;

    private $options;

    private function __construct()
    {
        $this->options = wpmbt_options::GetInstance();
        if (!class_exists('Requests')) {
            require_once ABSPATH . WPINC . '/class-requests.php';
            Requests::register_autoloader();
        }
    }

    public static function GetInstance()
    {
        if (sync_server::$instance == null)
            sync_server::$instance = new sync_server();
        return sync_server::$instance;
    }

    function Call_API_post_curl($url, $data)
    {
        $data_string = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );

        $result = curl_exec($ch);
        return $result;
    }

    function CallAPI($method, $url, $data = false)
    {
        $curl = curl_init();

        switch ($method) {
            case "POST":
                curl_setopt($curl, CURLOPT_POST, 1);

                if (!is_null($data)) {
                    //curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
                }
                break;
            case "PUT":
                curl_setopt($curl, CURLOPT_PUT, 1);
                break;
            default:
                if ($data)
                    $url = sprintf("%s?%s", $url, http_build_query($data));
        }

        // Optional Authentication:
        //curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        //curl_setopt($curl, CURLOPT_USERPWD, "username:password");

        curl_setopt($curl, CURLOPT_URL, $url);//$url
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $result = curl_exec($curl);
        if ($result === false) {
            throw new Exception(curl_error($curl), curl_errno($curl));
        }
        curl_close($curl);
        return $result;
    }

    public function login($user_name, $password)
    {
        $send_arr = [
            'username' => $user_name,
            'password' => $password
        ];
        $data = $send_arr;
        $response = $this->Call_API_post_request($this->options->options['bz_mahak_url'] . '/sync/Login', $data);// 'http://bazaraservices.mahaksoft.com:8081/sync/login'
        if ($response !== false) {
            $response = json_decode($response);
            if ($response->result === 'success') {
                $this->options->options['token'] = $response->data->token;
                return $response->data;
            };
            return false;
        }
    }

    private function Call_API_post_request($url, $data)
    {
        $data_string = json_encode($data);

        $request = Requests::request($url, $this->get_header(), $data_string, Requests::POST, $this->get_options());

        //$request->setRawPostData( $data_string );
        $result = $request->body;

        return $result;
    }

    function get_header()
    {

        return ["content-type" => "application/json"];
    }

    function get_options()
    {

        return ['timeout' => 20000];

    }

    public function get_products()
    {
        $send_arr = [
            'systemSyncID' => $this->options->options['bz_db_id'],
            'changeAfter' => $this->options->options['last_sync_time'],
            'userToken' => $this->options->options['token']
        ];
        $data = $send_arr;
        $response = $this->Call_API_post_request($this->options->options['bz_mahak_url'] . '/sync/GetProducts', $data);
        if ($response) {
            $response = json_decode($response);
            if ($response->result === 'success') {
                return $response->data;
            };
        };
        return false;
    }

    public function get_prices()
    {
        $send_arr = [
            'systemSyncID' => $this->options->options['bz_db_id'],
            'changeAfter' => $this->options->options['last_sync_time'],
            'userToken' => $this->options->options['token']
        ];
        $data = $send_arr;
        $response = $this->Call_API_post_request($this->options->options['bz_mahak_url'] . '/sync/GetPrices', $data);
        if ($response) {
            $response = json_decode($response);
            if ($response->result === 'success') {
                return $response->data;
            };
        };
        return false;
    }

    public function get_images()
    {
        $send_arr = [
            'systemSyncID' => $this->options->options['bz_db_id'],
            'changeAfter' => $this->options->options['last_sync_time'],
            'userToken' => $this->options->options['token']
        ];
        $data = $send_arr;
        $response = $this->Call_API_post_request($this->options->options['bz_mahak_url'] . '/sync/GetImages', $data);
        if ($response) {
            $response = json_decode($response);
            if ($response->result === 'success') {
                return $response->data;
            };
        };
        return false;
    }

    public function get_customers()
    {
        $send_arr = [
            'systemSyncID' => $this->options->options['bz_db_id'],
            'changeAfter' => $this->options->options['last_sync_time'],
            'userToken' => $this->options->options['token']
        ];
        $data = $send_arr;
        $response = $this->Call_API_post_request($this->options->options['bz_mahak_url'] . '/sync/GetPeople', $data);
        if ($response) {
            $response = json_decode($response);
            if ($response->result === 'success') {
                return $response->data;
            };
        };
        return false;
    }
}