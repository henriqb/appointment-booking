<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;

/**
 * Class SentNotification
 * @package Bookly\Lib\Entities
 */
class SentNotification extends Lib\Entity
{
    protected static $table = 'ab_sent_notifications';

    protected static $schema = array(
        'id'                      => array( 'format' => '%d' ),
        'customer_appointment_id' => array( 'format' => '%d' ),
        'staff_id'                => array( 'format' => '%d' ),
        'gateway'                 => array( 'format' => '%s', 'default' => 'email' ),
        'type'                    => array( 'format' => '%s' ),
        'created'                 => array( 'format' => '%s' ),
    );

}