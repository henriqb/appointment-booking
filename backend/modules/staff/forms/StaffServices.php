<?php
namespace Bookly\Backend\Modules\Staff\Forms;

use Bookly\Lib;

/**
 * Class StaffServices
 * @package Bookly\Backend\Modules\Staff\Forms
 */
class StaffServices extends Lib\Form
{
    protected static $entity_class = 'StaffService';

    /**
     * @var Lib\Entities\Category[]
     */
    private $collection = array();

    /**
     * @var array
     */
    private $selected = array();

    /**
     * @var array
     */
    private $category_services = array();

    /**
     * @var array
     */
    private $uncategorized_services = array();

    public function configure()
    {
        $this->setFields( array( 'price', 'service', 'staff_id', 'capacity' ) );
    }

    public function load( $staff_id )
    {
        $data = Lib\Entities\Category::query( 'c' )
            ->select( 'c.name AS category_name, s.*' )
            ->innerJoin( 'Service', 's', 's.category_id = c.id' )
            ->sortBy( 'c.position, s.position' )
            ->fetchArray();
        if ( !$data ) {
            $data = array();
        }

        $this->uncategorized_services = Lib\Entities\Service::query( 's' )->where( 's.category_id', null )->fetchArray();

        $staff_services = Lib\Entities\StaffService::query( 'ss' )
            ->select( 'ss.service_id, ss.price, ss.capacity' )
            ->where( 'ss.staff_id', $staff_id )
            ->fetchArray();
        if ( $staff_services ) {
            foreach ( $staff_services as $staff_service ) {
                $this->selected[ $staff_service['service_id'] ] = array( 'price' => $staff_service['price'], 'capacity' => $staff_service['capacity'] );
            }
        }

        foreach ( $data as $row ) {
            if ( ! isset( $this->collection[ $row['category_id'] ] ) ) {
                $abCategory = new Lib\Entities\Category();
                $abCategory->set( 'id', $row['category_id'] );
                $abCategory->set( 'name', $row['category_name'] );
                $this->collection[ $row['category_id'] ] = $abCategory;
            }
            unset( $row['category_name'] );

            $abService = new Lib\Entities\Service( $row );
            $this->category_services[ $row['category_id'] ][] = $abService->get( 'id' );
            $this->collection[ $row['category_id'] ]->addService( $abService );
        }
    }

    public function save()
    {
        $staff_id = $this->data['staff_id'];
        if ( $staff_id ) {
            Lib\Entities\staffService::query()->delete()->where( 'staff_id', $staff_id )->execute();
            if ( isset( $this->data['service'] ) ) {
                foreach ( $this->data['service'] as $service_id ) {
                    $staffService = new Lib\Entities\StaffService();
                    $staffService->set( 'service_id', $service_id );
                    $staffService->set( 'staff_id',   $staff_id );
                    $staffService->set( 'price',      $this->data['price'][ $service_id ] );
                    $staffService->set( 'capacity',   $this->data['capacity'][ $service_id ] );
                    $staffService->save();
                }
            }
        }
    }

    /**
     * @return Lib\Entities\Category[]|array
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @return array
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * @return array
     */
    public function getUncategorizedServices()
    {
      return $this->uncategorized_services;
    }

}
