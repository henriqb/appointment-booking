<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;

/**
 * Class Holiday
 * @package Bookly\Lib\Entities
 */
class Holiday extends Lib\Entity
{
    protected static $table = 'ab_holidays';

    protected static $schema = array(
        'id'           => array( 'format' => '%d' ),
        'staff_id'     => array( 'format' => '%d' ),
        'parent_id'    => array( 'format' => '%d' ),
        'date'         => array( 'format' => '%s' ),
        'repeat_event' => array( 'format' => '%s' ),
    );

}
