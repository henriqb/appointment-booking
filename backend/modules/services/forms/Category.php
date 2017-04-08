<?php
namespace Bookly\Backend\Modules\Services\Forms;

/**
 * Class Category
 * @package Bookly\Backend\Modules\Services\Forms
 */
class Category extends \Bookly\Lib\Form
{
    protected static $entity_class = 'Category';

    /**
     * Configure the form.
     */
    public function configure()
    {
        $this->setFields( array( 'name' ) );
    }

}
