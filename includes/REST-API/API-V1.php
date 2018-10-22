<?php

/**
 *
 * Get The latest post from a category !
 * @param array $params Options for the function.
 * @return string|null Post title for the latest,? * or null if none
 *
 */

function get_update_info()
{
    $result = [
        'status' => 'success',
        'message' => '',
        'data' => null,
        'result' => []
    ];
    try {
        $sync = sync::GetInstance();
        try {
            //echo "start update record function\n";
            //$result['data'] =
            $data = $sync->get_update_info();
            $result['result']['time'] = time();
            $result['result']['count_products'] = $data['count_products'];
            $result['result']['count_customers'] = $data['count_customers'];
            $result['result']['time_format'] = date("Y-M-d h:i:s");
        } catch (Exception $ex) {
            $result['message'] = '[ERROR] ' . $ex->getCode() . ' :' . $ex->getMessage();
        }
    } catch (Exception $ex) {
        $result['message'] = '[ERROR] ' . $ex->getCode() . ' :' . $ex->getMessage();
    }

    return $result;
    //   return $post[0]->post_title;
}


function get_sync_log_list()
{
    $result = [
        'status' => 'success',
        'message' => '',
        'data' => null,
        'result' => []
    ];
    try {
        //echo "start update record function\n";
        //$result['data'] =
        $log = log::get_instance();
        $data = $log->get('sync');
        if(is_string($data))
        { $result['status'] = 'error';
            $result['message'] = '[ERROR]  :' .$data;
        }else {
            $result['data'] = $data;
            $result['status'] = 'success';
        }
    } catch (Exception $ex) {
        $result['status'] = 'error';
        $result['message'] = '[ERROR] ' . $ex->getCode() . ' :' . $ex->getMessage();
    }


    return $result;
    //   return $post[0]->post_title;
}

function hit_update_records($params)
{
//    $post = get_posts( array(
//        'category'      => $category,
//        'posts_per_page'  => 1,
//        'offset'      => 0
//    ) );
//
//    if( empty( $post ) ){
//        return null;
//    }

    //echo "start update sync\n";
    $result = [
        'status' => 'success',
        'message' => '',
        'data' => null,
        'result' => []
    ];
    try {
        $sync = sync::GetInstance();
        try {
            //echo "start update record function\n";
            //$result['data'] =
            $data = $sync->update_records();
            //$result['data'] = $data;
            $result['result']['time'] = time();
            $result['result']['count_products'] = count($data);
            $result['result']['count_customers'] = count($data);
            $result['result']['time_format'] = date("Y-M-d h:i:s");
        } catch (Exception $ex) {
            $result['message'] = '[ERROR] ' . $ex->getCode() . ' :' . $ex->getMessage();
        }
    } catch (Exception $ex) {
        $result['message'] = '[ERROR] ' . $ex->getCode() . ' :' . $ex->getMessage();
    }

    return $result;
    //   return $post[0]->post_title;
}

function hit_sync()
{
    $result = [
        'status' => 'success',
        'message' => '',
        'data' => null,
        'result' => []
    ];
    try {
        $sync = sync::GetInstance();
        try {
            //$result['data'] =
            $data = $sync->get_sync('user');
            $result['result']['time'] = time();
            $result['result']['count_products'] = count($data['get_products']['products']);
            $result['result']['count_customers'] = count($data['get_customers']['customers']);
            $result['result']['time_format'] = date("Y-M-d h:i:s");
        } catch (Exception $ex) {
            $result['message'] = '[ERROR] ' . $ex->getCode() . ' :' . $ex->getMessage();
        }
    } catch (Exception $ex) {
        $result['message'] = '[ERROR] ' . $ex->getCode() . ' :' . $ex->getMessage();
    }

    return $result;
}

function test($param)
{
    return ['result' => true];
}