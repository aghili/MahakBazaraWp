<?php

/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 10/7/2018
 * Time: 11:09 AM
 */
interface int_shop_adapter
{
    public static function get_product($id_product);

    public static function create_product($data);

    public static function get_customer($id_customer);

    public static function create_customer($data);
}