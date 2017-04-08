<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $color = get_option( 'ab_appearance_color', '#f4662f' );
?>
<?php foreach ( $appointments as $app ) : ?>
    <tr>
        <?php foreach ( $columns as $column ) : ?>
            <?php
            switch ( $column ) :
                case 'date': ?>
                    <td><?php echo \Bookly\Lib\Utils\DateTime::formatDate( $app['start_date'] ) ?></td><?php
                    break;
                case 'time': ?>
                    <td><?php echo \Bookly\Lib\Utils\DateTime::formatTime( $app['start_date'] ) ?></td><?php
                    break;
                case 'price': ?>
                    <td><?php echo \Bookly\Lib\Utils\Common::formatPrice( $app['price'] ) ?></td><?php
                    break;
                case 'cancel': ?>
                    <td>
                    <?php if ( $app['start_date'] > current_time( 'mysql' ) ) : ?>
                        <?php if( $allow_cancel < strtotime( $app['start_date'] ) ) : ?>
                            <a class="ab-btn" style="background-color: <?php echo $color ?>" href="<?php echo esc_attr( $url_cancel . '&token=' . $app['token'] ) ?>">
                                <span class="ab_label"><?php _e( 'Cancel', 'bookly' ) ?></span>
                            </a>
                        <?php else : ?>
                            <span class="ab_label"><?php _e( 'Not allowed', 'bookly' ) ?></span>
                        <?php endif ?>
                    <?php else : ?>
                        <?php _e( 'Expired', 'bookly' ) ?>
                    <?php endif ?>
                    </td><?php
                    break;
                default : ?>
                    <td><?php echo $app[ $column ] ?></td>
            <?php endswitch ?>
        <?php endforeach ?>
    </tr>
<?php endforeach ?>