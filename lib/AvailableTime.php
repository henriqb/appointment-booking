<?php
namespace Bookly\Lib;

/**
 * Class AvailableTime
 * @package Bookly\Lib
 */
class AvailableTime
{
    /** @var \DateInterval */
    private $one_day = null;

    /** @var UserBookingData */
    private $userData;

    private $staffData = array();

    private $staff_ids = array();

    private $service = null;

    private $last_fetched_slot = null;

    private $selected_date = null;

    private $has_more_slots = false;

    private $slots = array();

    /**
     * Constructor.
     *
     * @param UserBookingData $userData
     */
    public function __construct( UserBookingData $userData )
    {
        $this->one_day   = new \DateInterval( 'P1D' );
        $this->userData  = $userData;
        $this->service   = $userData->getCartService();
        $this->staff_ids = array_merge( $userData->get( 'staff_ids' ), array( 0 ) );
    }

    /**
     * Load and init.
     */
    public function load()
    {
        $slots               = 0; // number of handled slots
        $groups              = 0; // number of handled groups
        $show_calendar       = Config::showCalendar();
        $show_day_per_column = Config::showDayPerColumn();
        $time_slot_length    = Config::getTimeSlotLength();
        $client_diff         = get_option( 'ab_settings_use_client_time_zone' )
            ? get_option( 'gmt_offset' ) * HOUR_IN_SECONDS + $this->userData->get( 'time_zone_offset' ) * 60
            : 0;
        /**
         * @var int $req_timestamp
         * @var \DateTime $date
         * @var \DateTime $max_date
         */
        list ( $req_timestamp, $date, $max_date ) = $this->_prepareDates();

        // Prepare staff data.
        $this->_prepareStaffData( $date );

        // The main loop.
        while ( ( $date = $this->_findAvailableDay( $date, $max_date ) ) && (
            $show_calendar ||
            $show_day_per_column && $groups < 10 ||   // one group/column
            ! $show_day_per_column && $slots < 100    // 10 slots/column * 10 columns
        ) ) {
            foreach ( $this->_findAvailableTime( $date ) as $frame ) {
                // Loop from start to:
                //   1. end minus time slot length when 'blocked' or 'not_full' is set.
                //   2. end minus service duration when nothing is set.
                $end = null;
                if ( isset ( $frame['blocked'] ) || isset ( $frame['not_full'] ) ) {
                    $end = $frame['end'] - $time_slot_length;
                } else {
                    $end = $frame['end'] - $this->service->get( 'duration' );
                }
                for ( $time = $frame['start']; $time <= $end; $time += $time_slot_length ) {

                    $timestamp        = $date->getTimestamp() + $time;
                    $client_timestamp = $timestamp - $client_diff;

                    if ( $client_timestamp < $req_timestamp ) {
                        // When we start 1 day before the requested date we may not need all found slots,
                        // we should skip those slots which do not fit the requested date in client's time zone.
                        continue;
                    }

                    $group = date( 'Y-m-d', ( $this->isAllDayService() && ! $show_calendar )
                        ? strtotime( 'first day of this month', $client_timestamp )     // group slots by months
                        : intval( $client_timestamp / DAY_IN_SECONDS ) * DAY_IN_SECONDS // group slots by days
                    );

                    if ( ! isset ( $this->slots[ $group ] ) ) {
                        $this->slots[ $group ] = array();
                        ++ $slots;
                        ++ $groups;
                    }

                    // Create/update slots.
                    if ( ! isset ( $this->slots[ $group ][ $client_timestamp ] ) ) {
                        $this->slots[ $group ][ $client_timestamp ] = array(
                            'timestamp' => $timestamp,
                            'staff_id'  => $frame['staff_id'],
                            'blocked'   => isset ( $frame['blocked'] ),
                        );
                        ++ $slots;
                    } elseif ( ! isset ( $frame['blocked'] ) ) {
                        if ( $this->slots[ $group ][ $client_timestamp ]['blocked'] ) {
                            // Set slot to available if it was marked as 'blocked' before.
                            $this->slots[ $group ][ $client_timestamp ]['staff_id'] = $frame['staff_id'];
                            $this->slots[ $group ][ $client_timestamp ]['blocked']  = false;
                        }
                        // Change staff member for this slot if the other staff member has higher price.
                        elseif ( $this->staffData[ $this->slots[ $group ][ $client_timestamp ]['staff_id'] ]['price'] < $this->staffData[ $frame['staff_id'] ]['price'] ) {
                            $this->slots[ $group ][ $client_timestamp ]['staff_id'] = $frame['staff_id'];
                        }
                    }
                }
            }

            $date->add( $this->one_day );
        }

        // Detect if there are more slots.
        if ( ! $show_calendar && $date !== false ) {
            while ( $date = $this->_findAvailableDay( $date, $max_date ) ) {
                $available_time = $this->_findAvailableTime( $date );
                if ( ! empty ( $available_time ) ) {
                    $this->has_more_slots = true;
                    break;
                }
                $date->add( $this->one_day );
            }
        }
    }

    /**
     * Determine requested timestamp and start and max date.
     *
     * @return array
     */
    private function _prepareDates()
    {
        if ( $this->last_fetched_slot ) {
            $start_date = new \DateTime( substr( $this->last_fetched_slot, 0, 10 ) );
            $req_timestamp = $start_date->getTimestamp();
            // The last_fetched_slot is always in WP time zone (see \Bookly\Frontend\Modules\Booking\Controller::executeRenderNextTime()).
            // We increase it by 1 day to get the date to start with.
            $start_date->add( $this->one_day );
        } else {
            $start_date = new \DateTime( $this->selected_date ? $this->selected_date : $this->userData->get( 'date_from' ) );
            if ( Config::showCalendar() ) {
                // Get slots for selected month.
                $start_date->modify( 'first day of this month' );
            }
            $req_timestamp = $start_date->getTimestamp();
            if ( get_option( 'ab_settings_use_client_time_zone' ) ) {
                // The userData::date_from is in client's time zone so we need to check the previous day too
                // because some available slots can be found in the previous day due to time zone offset.
                $start_date->sub( $this->one_day );
            }
        }

        $max_date = date_create(
            '@' . ( (int)current_time( 'timestamp' ) + Config::getMaximumAvailableDaysForBooking() * DAY_IN_SECONDS )
        )->setTime( 0, 0 );
        if ( Config::showCalendar() ) {
            $next_month = clone $start_date;
            if ( get_option( 'ab_settings_use_client_time_zone' ) ) {
                // Add one day since it was subtracted hereinabove.
                $next_month->add( $this->one_day );
            }
            $next_month->modify( 'first day of next month' );
            if ( $next_month < $max_date ) {
                $max_date = $next_month;
            }
        }

        return array( $req_timestamp, $start_date, $max_date );
    }

    /**
     * Find a day which is available for booking based on
     * user requested set of days.
     *
     * @access private
     * @param \DateTime $date
     * @param \DateTime $max_date
     * @return \DateTime
     */
    private function _findAvailableDay( \DateTime $date, \DateTime $max_date )
    {
        $attempt = 0;
        // Find available day within requested days.
        $requested_days = $this->userData->get( 'days' );
        while ( ! in_array( intval( $date->format( 'w' ) ) + 1, $requested_days ) ) {
            $date->add( $this->one_day );
            if ( ++ $attempt >= 7 ) {
                return false;
            }
        }

        return $date >= $max_date ? false : $date;
    }

    /**
     * Find array of time slots available for booking
     * for given date.
     *
     * @access private
     * @param \DateTime $date
     * @return array
     */
    private function _findAvailableTime( \DateTime $date )
    {
        $result             = array();
        $time_slot_length   = Config::getTimeSlotLength();
        $prior_time         = Config::getMinimumTimePriorBooking();
        $current_timestamp  = (int) current_time( 'timestamp' ) + $prior_time;
        $current_date       = date_create( '@' . $current_timestamp )->setTime( 0, 0 );
        $is_all_day_service = $this->isAllDayService();
        $request_extras_duration = apply_filters( 'bookly_extras_duration', 0, $this->userData->get( 'extras' ) );

        if ( $date < $current_date ) {
            return array();
        }

        $day_of_week = intval( $date->format( 'w' ) ) + 1; // 1-7
        $start_time  = date( 'H:i:s', ceil( $current_timestamp / $time_slot_length ) * $time_slot_length );

        foreach ( $this->staffData as $staff_id => $staff ) {

            if ( $staff['capacity'] < $this->userData->get( 'number_of_persons' ) ) {
                continue;
            }

            if ( isset ( $staff['working_hours'][ $day_of_week ] ) && $this->isWorkingDay( $date, $staff_id ) ) {

                if ( $is_all_day_service ) {
                    // For whole day services do not check staff working hours.
                    $intersections = array( array(
                        'start' => 0,
                        'end'   => DAY_IN_SECONDS,
                    ) );
                } else {
                    // Find intersection between working and requested hours
                    //(excluding time slots in the past).
                    $working_start_time = ( $date == $current_date && $start_time > $staff['working_hours'][ $day_of_week ]['start_time'] )
                        ? $start_time
                        : $staff['working_hours'][ $day_of_week ]['start_time'];

                    $intersections = $this->_findIntersections(
                        Utils\DateTime::timeToSeconds( $working_start_time ),
                        Utils\DateTime::timeToSeconds( $staff['working_hours'][ $day_of_week ]['end_time'] ),
                        Utils\DateTime::timeToSeconds( $this->userData->get( 'time_from' ) ),
                        Utils\DateTime::timeToSeconds( $this->userData->get( 'time_to' ) )
                    );
                }

                foreach ( $intersections as $intersection ) {
                    if ( $intersection['end'] - $intersection['start'] >= $this->service->get( 'duration' ) ) {
                        // Initialize time frames.
                        $frames = array( array(
                            'start'    => $intersection['start'],
                            'end'      => $intersection['end'],
                            'staff_id' => $staff_id
                        ) );
                        if ( ! $is_all_day_service ) {
                            // Remove breaks from time frames for non all day services only.
                            foreach ( $staff['working_hours'][ $day_of_week ]['breaks'] as $break ) {
                                $frames = $this->_removeTimePeriod(
                                    $frames,
                                    Utils\DateTime::timeToSeconds( $break['start'] ),
                                    Utils\DateTime::timeToSeconds( $break['end'] )
                                );
                            }
                        }
                        // Remove bookings from time frames.
                        foreach ( $staff['bookings'] as $booking ) {
                            // Work with bookings for the current day only.
                            if ( $date->format( 'Y-m-d' ) == $booking['start_date'] ) {

                                $frames = $this->_removeTimePeriod(
                                    $frames,
                                    $booking['start_time'] - $booking['padding_left'],
                                    $booking['end_time'] + $booking['padding_right'],
                                    $removed
                                );

                                if ( $removed ) {
                                    // Handle not full bookings (when number of bookings is less than capacity).
                                    if (
                                        $booking['from_google'] == false &&
                                        $booking['service_id'] == $this->userData->get( 'service_id' ) &&
                                        $booking['start_time'] >= $intersection['start'] &&
                                        $staff['capacity'] - $booking['number_of_bookings'] >= $this->userData->get( 'number_of_persons' )
                                    ) {
                                        $exist_extras_duration = apply_filters( 'bookly_extras_duration', 0, (array) json_decode( $booking['extras'], true ) );
                                        if ( $exist_extras_duration >= $request_extras_duration ) {
                                            // Show the first slot as available.
                                            $frames[] = array(
                                                'start'    => $booking['start_time'],
                                                'end'      => $booking['start_time'] + $time_slot_length,
                                                'staff_id' => $staff_id,
                                                'not_full' => true,
                                            );
                                        }
                                    }
                                    if ( $is_all_day_service ) {
                                        // For all day services we break the loop since there can be
                                        // just 1 booking per day for such services.
                                        break;
                                    }
                                }
                            }
                        }
                        $result = array_merge( $result, $frames );
                    }
                }
            }
        }
        usort( $result, function ( $a, $b ) { return $a['start'] - $b['start']; } );

        return $result;
    }

    /**
     * Checks if the date is not a holiday for this employee
     *
     * @param \DateTime $date
     * @param int $staff_id
     *
     * @return bool
     */
    private function isWorkingDay( \DateTime $date, $staff_id )
    {
        $working_day = true;
        if ( $this->staffData[ $staff_id ]['holidays'] ) {
            if ( array_key_exists( $date->format( 'Y-m-d' ), $this->staffData[ $staff_id ]['holidays'] )
                || array_key_exists( $date->format( 'm-d' ), $this->staffData[ $staff_id ]['holidays'] ) )
            {
                return false;
            }
        }

        return $working_day;
    }

    /**
     * Find intersection between 2 time periods.
     *
     * @param mixed $p1_start
     * @param mixed $p1_end
     * @param mixed $p2_start
     * @param mixed $p2_end
     * @return array
     */
    private function _findIntersections( $p1_start, $p1_end, $p2_start, $p2_end )
    {
        $result = array();

        if ( $p2_start > $p2_end ) {
            $result[] = $this->_findIntersections( $p1_start, $p1_end, $p2_start, 86400 );
            $result[] = $this->_findIntersections( $p1_start, $p1_end, 0, $p2_end );
        } else {
            if ( $p1_start <= $p2_start && $p1_end > $p2_start && $p1_end <= $p2_end ) {
                $result[] = array( 'start' => $p2_start, 'end' => $p1_end );
            } elseif ( $p1_start <= $p2_start && $p1_end >= $p2_end ) {
                $result[] = array( 'start' => $p2_start, 'end' => $p2_end );
            } elseif ( $p1_start >= $p2_start && $p1_start < $p2_end && $p1_end >= $p2_end ) {
                $result[] = array( 'start' => $p1_start, 'end' => $p2_end );
            } elseif ( $p1_start >= $p2_start && $p1_end <= $p2_end ) {
                $result[] = array( 'start' => $p1_start, 'end' => $p1_end );
            }
        }

        return $result;
    }

    /**
     * Remove time period from the set of time frames.
     *
     * @param array $frames
     * @param mixed $p_start
     * @param mixed $p_end
     * @param bool& $removed  Whether the period was removed or not
     * @return array
     */
    private function _removeTimePeriod( array $frames, $p_start, $p_end, &$removed = false )
    {
        $show_blocked_slots = Config::showBlockedTimeSlots();
        $service_duration   = $this->service->get( 'duration' );
        $is_all_day_service = $this->isAllDayService();

        $result  = array();
        $removed = false;

        foreach ( $frames as $frame ) {
            $intersections = $this->_findIntersections(
                $frame['start'],
                $frame['end'],
                $p_start,
                $p_end
            );
            foreach ( $intersections as $intersection ) {
                $blocked_start = $frame['start'];
                $blocked_end   = $frame['end'];
                if ( $intersection['start'] - $frame['start'] >= $service_duration ) {
                    $result[] = array_merge( $frame, array(
                        'end' => $intersection['start'],
                    ) );
                    $blocked_start = $intersection['start'];
                }
                if ( $frame['end'] - $intersection['end'] >= $service_duration ) {
                    $result[] = array_merge( $frame, array(
                        'start' => $intersection['end'],
                    ) );
                    $blocked_end = $intersection['end'];
                }
                if ( $show_blocked_slots ) {
                    // Show removed period as 'blocked'.
                    $result[] = array_merge( $frame, array(
                        'start'   => $blocked_start,
                        'end'     => $is_all_day_service ? Config::getTimeSlotLength() : $blocked_end,
                        'blocked' => true,
                    ) );
                }
            }
            if ( empty ( $intersections ) ) {
                $result[] = $frame;
            } else {
                $removed = true;
            }
        }

        return $result;
    }

    /**
     * Prepare data for staff.
     *
     * @param \DateTime $start_date
     */
    private function _prepareStaffData( \DateTime $start_date )
    {
        $this->staffData = array();

        $services = Entities\StaffService::query( 'ss' )
            ->select( 'ss.staff_id, ss.price, ss.capacity' )
            ->whereIn( 'ss.staff_id', $this->staff_ids )
            ->where( 'ss.service_id', $this->userData->get( 'service_id' ) )
            ->fetchArray();

        foreach ( $services as $item ) {
            $this->staffData[ $item['staff_id'] ] = array(
                'price'         => $item['price'],
                'capacity'      => $item['capacity'],
                'holidays'      => array(),
                'bookings'      => array(),
                'working_hours' => array(),
            );
        }

        // Load holidays.
        $holidays = Entities\Holiday::query( 'h' )->select( 'IF(h.repeat_event, DATE_FORMAT(h.date, \'%%m-%%d\'), h.date) as date, h.staff_id' )
            ->whereIn( 'h.staff_id', $this->staff_ids )
            ->whereRaw( 'h.repeat_event = 1 OR h.date >= %s', array( $start_date->format( 'Y-m-d H:i:s' ) ) )
            ->fetchArray();
        foreach ( $holidays as $item ) {
            $this->staffData[ $item['staff_id'] ]['holidays'][ $item['date'] ] = 1;
        }

        // Load working schedule.
        $working_schedule = Entities\StaffScheduleItem::query( 'ssi' )
            ->select( 'ssi.*, break.start_time AS break_start, break.end_time AS break_end' )
            ->leftJoin( 'ScheduleItemBreak', 'break', 'break.staff_schedule_item_id = ssi.id' )
            ->whereIn( 'ssi.staff_id', $this->staff_ids )
            ->whereNot( 'ssi.start_time', null )
            ->fetchArray();

        foreach ( $working_schedule as $item ) {
            if ( ! isset ( $this->staffData[ $item['staff_id'] ]['working_hours'][ $item['day_index'] ] ) ) {
                $this->staffData[ $item['staff_id'] ]['working_hours'][ $item['day_index'] ] = array(
                    'start_time' => $item['start_time'],
                    'end_time'   => $item['end_time'],
                    'breaks'     => array(),
                );
            }
            if ( $item['break_start'] ) {
                $this->staffData[ $item['staff_id'] ]['working_hours'][ $item['day_index'] ]['breaks'][] = array(
                    'start' => $item['break_start'],
                    'end'   => $item['break_end']
                );
            }
        }

        $padding_left  = (int) $this->service->get( 'padding_left' );
        $padding_right = (int) $this->service->get( 'padding_right' );
        // Load bookings.
        $bookings = Entities\CustomerAppointment::query( 'ca' )
            ->select( 'a.id, a.staff_id, a.service_id, a.google_event_id, a.start_date, DATE_ADD(a.end_date, INTERVAL a.extras_duration SECOND) AS end_date, ca.extras,
                       COALESCE(s.padding_left,0) + ' . $padding_right . ' AS padding_left,
                       COALESCE(s.padding_right,0) + ' . $padding_left . ' AS padding_right,
                       SUM(ca.number_of_persons) AS number_of_bookings' )
            ->leftJoin( 'Appointment', 'a', 'a.id = ca.appointment_id' )
            ->leftJoin( 'StaffService', 'ss', 'ss.staff_id = a.staff_id AND ss.service_id = a.service_id' )
            ->leftJoin( 'Service', 's', 's.id = a.service_id' )
            ->whereIn( 'a.staff_id', $this->staff_ids )
            ->whereGte( 'a.start_date', $start_date->format( 'Y-m-d' ) )
            ->groupBy( 'a.start_date' )->groupBy( 'a.staff_id' )->groupBy( 'a.service_id' )
            ->fetchArray();
        foreach ( $bookings as $item ) {
            $item['from_google'] = false;
            list ( $s_date, $s_time ) = explode( ' ', $item['start_date'] );
            list ( $e_date, $e_time ) = explode( ' ', $item['end_date'] );
            $item['start_date'] = $s_date;
            $item['start_time'] = Utils\DateTime::timeToSeconds( $s_time );
            unset ( $item['end_date'] );
            $item['end_time']   = Utils\DateTime::timeToSeconds( $e_time );
            if ( $s_date != $e_date ) {
                // Add 24 hours for bookings that end on the next day.
                $item['end_time'] += DAY_IN_SECONDS;
            }
            $this->staffData[ $item['staff_id'] ]['bookings'][] = $item;
        }
        // Handle cart bookings
        if ( get_option( 'ab_settings_step_cart_enabled' ) ) {
            $cart_key = $this->userData->getCartKey();
            foreach ( $this->userData->get( 'cart' ) as $key => $cart_item ) {
                if ( array_key_exists( $cart_item['staff_id'], $this->staffData ) ) {
                    if ( $key != $cart_key ) {
                        $this->userData->setCartKey( $key );
                        list ( $s_date, $s_time ) = explode( ' ', $cart_item['appointment_datetime'] );
                        $service = $this->userData->getCartService();

                        $end_time = date_create( $cart_item['appointment_datetime'] )->modify( $service->get( 'duration' ) . ' seconds' );
                        $cart_item['end_time'] = Utils\DateTime::timeToSeconds( $end_time->format( 'H:i' ) );
                        if ( $s_date != $end_time->format( 'Y-m-d' ) ) {
                            // Add 24 hours for bookings that end on the next day.
                            $cart_item['end_time'] += DAY_IN_SECONDS;
                        }
                        $start_time = Utils\DateTime::timeToSeconds( $s_time );
                        if ( array_key_exists( 'bookings', $this->staffData[ $cart_item['staff_id'] ] ) ) {
                            foreach ( $this->staffData[ $cart_item['staff_id'] ]['bookings'] as &$booking ) {
                                // If exist appointment for current staff increase number_of_persons
                                if ( $booking['service_id'] == $cart_item['service_id']
                                     && $booking['start_date'] == $s_date
                                     && $booking['start_time'] == $start_time
                                     && $booking['from_google'] == false
                                ) {
                                    $booking['number_of_bookings'] += $cart_item['number_of_persons'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $this->userData->setCartKey( $cart_key );
        }

        // Handle Google Calendar events.
        if  ( get_option( 'ab_settings_google_two_way_sync' ) ) {
            $query = Entities\Staff::query( 's' )->whereIn( 's.id', $this->staff_ids );
            foreach ( $query->find() as $staff ) {
                $google = new Google();
                if ( $google->loadByStaff( $staff ) ) {
                    $this->staffData[ $staff->get( 'id' ) ]['bookings'] = array_merge(
                        $this->staffData[ $staff->get( 'id' ) ]['bookings'],
                        $google->getCalendarEvents( $start_date ) ?: array()
                    );
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getSlots()
    {
        return $this->slots;
    }

    /**
     * Get disabled days in Pickadate format.
     *
     * @return array
     */
    public function getDisabledDaysForPickadate()
    {
        $result = array();
        $date = new \DateTime( $this->selected_date ? $this->selected_date : $this->userData->get( 'date_from' ) );
        $date->modify( 'first day of this month' );
        $end_date = clone $date;
        $end_date->modify( 'first day of next month' );
        $Y = (int) $date->format( 'Y' );
        $n = (int) $date->format( 'n' ) - 1;
        while ( $date < $end_date ) {
            if ( ! array_key_exists( $date->format( 'Y-m-d' ), $this->slots ) ) {
                $result[] = array( $Y, $n, (int) $date->format( 'j' ) );
            }
            $date->add( $this->one_day );
        }

        return $result;
    }

    public function setLastFetchedSlot( $last_fetched_slot )
    {
        $this->last_fetched_slot = $last_fetched_slot;
    }

    public function setSelectedDate( $selected_date )
    {
        $this->selected_date = $selected_date;
    }

    public function getSelectedDateForPickadate()
    {
        if ( $this->selected_date ) {
            foreach ( $this->slots as $group => $slots ) {
                if ( $group >= $this->selected_date ) {
                    return $group;
                }
            }

            if ( empty( $this->slots ) ) {
                return $this->selected_date;
            } else {
                reset( $this->slots );
                return key( $this->slots );
            }
        }

        if ( ! empty ( $this->slots ) ) {
            reset( $this->slots );
            return key( $this->slots );
        }

        return $this->userData->get( 'date_from' );
    }

    public function hasMoreSlots()
    {
        return $this->has_more_slots;
    }

    /**
     * Check whether the service is all day or not.
     * An all day service has duration set to 86400 seconds.
     *
     * @return bool
     */
    public function isAllDayService()
    {
        return $this->service->get( 'duration' ) == DAY_IN_SECONDS;
    }

}