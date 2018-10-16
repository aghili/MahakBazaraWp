<?php

/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 10/1/2018
 * Time: 9:26 AM
 */
class bazara_price implements \Serializable
{
    private $price;

    public function __construct($price_bazara = null)
    {
        if ($price_bazara !== null) {
            $this->price = $price_bazara;
            //get_base_info($price_bazara);
        }
        //$this->done = false;
    }

    public function get_row_version()
    {
        return $this->price->RowVersion;
    }

    public function get_id()
    {
        return $this->price->ProductID;
    }

    public function get_date_modified()
    {
        return $this->price->UpdateDate;
    }

    public function get_stock_quantity()
    {
        //todo:return price weight in wp format
        return $this->price->AvailableCount;
    }

    public function get_sale_price()
    {
        //todo:return price sale value in wp format
        return $this->get_regular_price();
    }

    public function get_regular_price()
    {
        //todo:return price reqular value in wp format
        return $this->price->{"Price" . $this->price->DefaultPriceLevel};
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
        return (serialize($this->price));
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
        $this->price = unserialize($serialized);
    }
}