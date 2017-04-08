(function($) {
    window.booklyCustomerProfile = function(options) {
       $('.ab--show-past').on('click', function(e) {
           e.preventDefault();
           var $self = $(this),
               $table = $self.prev('table.ab-appointments-table'),
               ladda = Ladda.create(this);
           ladda.start();
           $.get(options.ajaxurl, {action: 'ab_get_past_appointments', columns: $table.data('columns'), page: $table.data('page') + 1 }, function () {
           }, 'json').done(function (resp) {
               ladda.stop();
               if (resp.data.more) {
                   $self.find('span.ab_label').html(BooklyL10n.show_more);
               } else {
                   $self.remove();
               }
               if (resp.data.html) {
                   $(resp.data.html).hide().appendTo($table).show('slow');
                   $table.data('page', $table.data('page') + 1 );
               }
           });
       });
    };
})(jQuery);