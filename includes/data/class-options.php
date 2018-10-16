<?php

/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 7/14/2018
 * Time: 2:52 PM
 */
class wpmbt_options extends base_info
{
    private static $instance = null;

    public $options;

    private function __construct()
    {
        $this->load();
    }

    public function load()
    {
        if (!$this->options = get_option(wpmbt_options::$plugin_name))
            $this->options = [];

    }

    public static function  &GetInstance()
    {
        if (wpmbt_options::$instance == null) {
            wpmbt_options::$instance = new self();
        }
        return wpmbt_options::$instance;
    }

    public function save()
    {
        update_option(wpmbt_options::$plugin_name, $this->options, true);
    }
}