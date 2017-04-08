<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php if ( is_user_logged_in() ) : ?>
    <div class="ab-customer-appointments">
        <h2><?php _e( 'Appointments', 'bookly' ) ?></h2>
        <?php if ( ! empty( $appointments ) ) : ?>
            <?php if ( isset( $attributes['columns'] ) && $columns = explode( ',', $attributes['columns'] ) ) : ?>
                <table class="ab-appointments-table" data-columns="<?php echo esc_attr( json_encode( $columns ) ) ?>" data-page="0">
                    <?php if ( isset( $attributes['show_column_titles'] ) && $attributes['show_column_titles'] ) : ?>
                        <thead>
                            <tr>
                                <?php foreach ( $columns as $column ) : ?>
                                    <th><?php echo $titles[ $column ] ?></th>
                                <?php endforeach ?>
                            </tr>
                        </thead>
                    <?php endif ?>
                    <?php include '_row.php' ?>
                </table>
                <?php if( $more ) : ?>
                    <button class="ab-btn ab--show-past ab-inline-block ab-right" style="background: <?php echo $color ?>!important; width: auto" data-spinner-size="40" data-style="zoom-in">
                        <span class="ab_label"><?php _e( 'Show past appointments', 'bookly' ) ?></span>
                    </button>
                <?php endif ?>
            <?php endif ?>
        <?php else : ?>
            <p><?php _e( 'No appointments found', 'bookly' ) ?></p>
        <?php endif ?>
    </div>

    <script type="text/javascript">
        (function (win, fn) {
            var done = false, top = true,
                doc = win.document,
                root = doc.documentElement,
                modern = doc.addEventListener,
                add = modern ? 'addEventListener' : 'attachEvent',
                rem = modern ? 'removeEventListener' : 'detachEvent',
                pre = modern ? '' : 'on',
                init = function(e) {
                    if (e.type == 'readystatechange') if (doc.readyState != 'complete') return;
                    (e.type == 'load' ? win : doc)[rem](pre + e.type, init, false);
                    if (!done) { done = true; fn.call(win, e.type || e); }
                },
                poll = function() {
                    try { root.doScroll('left'); } catch(e) { setTimeout(poll, 50); return; }
                    init('poll');
                };
            if (doc.readyState == 'complete') fn.call(win, 'lazy');
            else {
                if (!modern) if (root.doScroll) {
                    try { top = !win.frameElement; } catch(e) { }
                    if (top) poll();
                }
                doc[add](pre + 'DOMContentLoaded', init, false);
                doc[add](pre + 'readystatechange', init, false);
                win[add](pre + 'load', init, false);
            }
        })(window, function() {
            window.booklyCustomerProfile({
                ajaxurl : <?php echo json_encode( $ajax_url ) ?>
            });
        });
    </script>
<?php else : ?>
    <?php wp_login_form() ?>
<?php endif ?>