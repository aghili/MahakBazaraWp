<?php

/**
 * Created by PhpStorm.
 * User: aghili
 * Date: 10/1/2018
 * Time: 9:26 AM
 */
class bazara_images implements \Serializable
{
    private $image;

    public function __construct($image_bazara = null)
    {
        if ($image_bazara !== null) {
            $this->image = $image_bazara;
            //get_base_info($price_bazara);
        }
        //$this->done = false;
    }

    public function get_images()
    {
        return '';
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
        return serialize($this->image);
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
        $this->image = unserialize($serialized);
    }
}