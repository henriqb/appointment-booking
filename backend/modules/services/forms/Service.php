<?php
namespace Bookly\Backend\Modules\Services\Forms;

/**
 * Class Service
 * @package Bookly\Backend\Modules\Services\Forms
 */
class Service extends \Bookly\Lib\Form
{
    protected static $entity_class = 'Service';

    public function configure()
    {
        $this->setFields( array( 'id', 'title', 'duration', 'price', 'category_id', 'color', 'capacity', 'padding_left', 'padding_right', 'info' ) );
    }

    /**
     * Bind values to form.
     *
     * @param array $_post
     * @param array $files
     */
    public function bind( array $_post, array $files = array() )
    {
        if ( array_key_exists( 'category_id', $_post ) && ! $_post['category_id'] ) {
            $_post['category_id'] = null;
        }
        parent::bind( $_post, $files );
    }

    public function save()
    {
        if ( $this->isNew() ) {
            // When adding new service - set its color randomly.
            $this->data['color'] = sprintf( '#%06X', mt_rand( 0, 0x64FFFF ) );
        }

        return parent::save();
    }

}