<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;

/**
 * Class Service
 * @package Bookly\Lib\Entities
 */
class Service extends Lib\Entity
{
    protected static $table = 'ab_services';

    protected static $schema = array(
        'id'            => array( 'format' => '%d' ),
        'title'         => array( 'format' => '%s' ),
        'duration'      => array( 'format' => '%d', 'default' => 900 ),
        'price'         => array( 'format' => '%.2f', 'default' => '0' ),
        'category_id'   => array( 'format' => '%d' ),
        'color'         => array( 'format' => '%s' ),
        'capacity'      => array( 'format' => '%d', 'default' => '1' ),
        'position'      => array( 'format' => '%d', 'default' => 9999 ),
        'padding_left'  => array( 'format' => '%d', 'default' => '0' ),
        'padding_right' => array( 'format' => '%d', 'default' => '0' ),
        'info'          => array( 'format' => '%s' ),
    );

    /**
     * Get title (if empty returns "Untitled").
     *
     * @return string
     */
    public function getTitle()
    {
        return Lib\Utils\Common::getTranslatedString( 'service_' . $this->get( 'id' ), $this->get( 'title' ) != '' ? $this->get( 'title' ) : __( 'Untitled', 'bookly' ) );
    }

    /**
     * Get category name.
     *
     * @return string
     */
    public function getCategoryName()
    {
        if ( $this->get( 'category_id' ) ) {
            $category = new Category();
            $category->load( $this->get( 'category_id' ) );

            return $category->getName();
        }

        return __( 'Uncategorized', 'bookly' );
    }

    public function getInfo()
    {
        return Lib\Utils\Common::getTranslatedString( 'service_' . $this->get( 'id' ) . '_info', $this->get( 'info' ) );
    }

}
