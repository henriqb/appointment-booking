<?php
namespace Bookly\Backend\Modules\Staff\Forms;

use Bookly\Lib;

/**
 * Class StaffMember
 * @package Bookly\Backend\Modules\Staff\Forms
 */
class StaffMember extends Lib\Form
{
    protected static $entity_class = 'Staff';

    protected $wp_users;

    // Help methods for rendering widgets.

    /**
     * Get list of users available for particular staff.
     *
     * @global string $table_prefix
     * @param integer $staff_id If null then it means new staff
     * @return array
     */
    public function getUsersForStaff( $staff_id = null )
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        if ( ! is_multisite() ) {
            $query = sprintf(
                'SELECT ID, user_email, display_name FROM ' . $wpdb->users . '
               WHERE ID NOT IN(SELECT DISTINCT IFNULL( wp_user_id, 0 ) FROM ' . Lib\Entities\Staff::getTableName() . ' %s)
               ORDER BY display_name',
                $staff_id !== null
                    ? 'WHERE ' . Lib\Entities\Staff::getTableName() . '.id <> ' . intval( $staff_id )
                    : ''
            );
            $users = $wpdb->get_results( $query );
        } else {
            // In Multisite show users only for current blog.
            if ( $staff_id == null ) {
                $query = Lib\Entities\Staff::query( 's' )->select( 'DISTINCT wp_user_id' )->whereNot( 'wp_user_id', null );
            } else {
                $query = Lib\Entities\Staff::query( 's' )->select( 'wp_user_id' )->whereNot( 'id', $staff_id );
            }
            $occupied_wp_users = array();
            foreach ( $query->fetchArray() as $staff ) {
                $occupied_wp_users[] = $staff['wp_user_id'];
            }
            $users = get_users( array( 'blog_id' => get_current_blog_id(), 'orderby' => 'display_name', 'exclude' => $occupied_wp_users ) );
        }

        return $users;
    }

}
