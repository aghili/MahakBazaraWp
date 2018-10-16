<?php

/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 10/7/2018
 * Time: 11:17 AM
 */
interface int_shop_customer
{
    public function save();

    public function remove();

    public function set_by_bazara($bazara_customer);

    #geters
    public function get_date_created();

    public function get_date_modified();

    public function get_email();

    public function get_first_name();

    public function get_last_name();

    public function get_display_name();

    public function get_role();

    public function get_username();


    #seters:

    public function set_date_created($value);

    public function set_date_modified($value);

    public function set_email($value);

    public function set_first_name($value);

    public function set_last_name($value);

    public function set_display_name($value);

    public function set_role($value);

    public function set_username($value);
}

/*todo:
 public function get_billing'            => array(
public function get_first_name();
public function get_last_name();
public function get_company();
public function get_address_1();
public function get_address_2();
public function get_city();
public function get_state();
public function get_postcode();
public function get_country();
public function get_email();
public function get_phone();
),
public function get_shipping'           => array(
public function get_first_name();
public function get_last_name();
public function get_company();
public function get_address_1();
public function get_address_2();
public function get_city();
public function get_state();
public function get_postcode();
public function get_country();
),
public function get_is_paying_customer();*/