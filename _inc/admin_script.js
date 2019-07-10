console.log('admin aetwp!');

jQuery(document).ready(function($) {
    $('#datepicker').datepicker({
        dateFormat: "dd.mm.yy"
    });

    var change = 0;
    $('#datepicker').change(function() {
        $('#date').text('change #'+(++change));

        const date_str = $(this).val();
        const parts = date_str.split('.');
        const dd = parts[0];
        const mm = parts[1];
        const yy = parts[2];
        // var date = new Date( 2007, 1 - 1, 26 );
        // var raw_date = $.datepicker.parseDate("dd.mm.yy", $(this).val(), {
        //     dateFormat: "yy-mm-dd"
        // });
        // const raw_date = yy+mm+dd;
        // const rfc_date = dd+' '+mm+' '+yy;
        const iso8601_date = yy+'-'+mm+'-'+dd;
        $('#extradate').val(iso8601_date);
    });
});

