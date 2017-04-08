<?php
namespace Bookly\Backend\Modules\Customers\Forms;

use Bookly\Lib;

/**
 * Class Customer
 * @package Bookly\Backend\Modules\Customers\Forms
 */
class Customer extends Lib\Form
{
    protected static $entity_class = 'Customer';

    public function configure()
    {
        $this->setFields( array(
            'name',
            'wp_user_id',
            'phone',
            'email',
            'notes'
        ) );
    }

}
