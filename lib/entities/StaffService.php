<?php
namespace Bookly\Lib\Entities;

use Bookly\Lib;

/**
 * Class StaffService
 * @package Bookly\Lib\Entities
 */
class StaffService extends Lib\Entity
{
    protected static $table = 'ab_staff_services';

    protected static $schema = array(
        'id'            => array( 'format' => '%d' ),
        'staff_id'      => array( 'format' => '%d' ),
        'service_id'    => array( 'format' => '%d' ),
        'price'         => array( 'format' => '%.2f', 'default' => '0' ),
        'capacity'      => array( 'format' => '%d', 'default' => '1' ),
    );

    /** @var Service */
    public $service = null;

}
