/* Add here all your JS customizations */

// basic
$('body').on('click', '.form-validate .submit', function(e){
    $(".form-validate").validate({
        highlight: function( label ) {
            $(label).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        success: function( label ) {
            $(label).closest('.form-group').removeClass('has-error');
            label.remove();
        },
        errorPlacement: function( error, element ) {
            var placement = element.closest('.input-group');
            if (!placement.get(0)) {
                placement = element;
            }
            if (error.text() !== '') {
                placement.after(error);
            }
        }
    });
});


/*
    Got the following solution form: https://github.com/eternicode/bootstrap-datepicker/issues/1978
    Thanks to the comment authors
 */
/**
 * Example: To display "Jan - Feb - Mar" months in a line, use:
 *      <div class='calendar'></div>
 *      <div class='calendar'></div>
 *      <div class='calendar'></div>
 *      <script>multipleDatePicker('.calendar');</script>
 *
 * @param elementSelector
 * @param selectDateCallback
 * @param startMonth
 * @param startYear
 * @param startDay
 */
function multipleDatePicker(elementSelector, selectDateCallback, startYear, startMonth, startDay) {

    var calendars = $(elementSelector);
    var currentDate = new Date(), currentDateMonth, currentDateDay;
    currentDate = (startYear ? new Date(currentDate.setFullYear(startYear)) : currentDate );
    currentDateMonth = ( startMonth ? new Date(currentDate.setMonth( startMonth-1 )) : currentDate );  // -1: as the month count starts from 0
    currentDateDay = ( startDay ? new Date(currentDateMonth.setDate( startDay )) : currentDateMonth );

    var monthNames = ["January", "February", "March", "April", "May", "June",
        "July", "August", "September", "October", "November", "December"
    ];

    console.log(startYear, startMonth, startDay, currentDateDay);

    $(elementSelector).map(function(index) {
        //var month = ( (calendars.length > 2) ?  currentDateMonth.getMonth() + index -1 : currentDateMonth.getMonth() + index );
        var month = currentDateMonth.getMonth() + index;

        $(this).datepicker({
            defaultViewDate: {
                year: currentDate.getFullYear(),
                month: month ,
                date: 1,
                day: currentDateDay.getDate()
            },
            //todayHighlight: true,
            //date: currentDateDay
        });

        if( (month+1) == startMonth ) {
            $('.day', this).each(function(){
                if( ($(this).text()  == startDay) && !($(this).hasClass('old')) && !($(this).hasClass('new')) ) {
                    $(this).addClass('selected');
                }
            });
        }

    });

    // keep month in sync
    var orderMonth = function(e) {
        var target = e.target;
        var date = e.date;
        var positionOfTarget = calendars.index(target);
        calendars.each(function(index) {
            var newDate = new Date(date);
            var $calendar = $(this);

            /*if (this === target) {
                if( monthNames[date.getMonth()] == monthNames[startMonth-1] ) {
                    $('.day', this).each(function(){
                        if( ($(this).text() == startDay) && !($(this).hasClass('old')) && !($(this).hasClass('new')) ) {
                            $(this).addClass('selected');
                            console.log( $calendar, startDay, $(this), $(this).text() );
                        }
                    });
                }

            } else {*/

                var month = date.getMonth() + index - positionOfTarget;
                newDate.setUTCDate(1);
                newDate.setMonth(month);

                $(this).datepicker('_setDate', newDate, 'view');

                console.log( $calendar, month, monthNames[month], monthNames[startMonth-1]);

                if (monthNames[month] == monthNames[startMonth - 1]) {
                    $('.day', this).each(function () {
                        if (($(this).text() == startDay) && !($(this).hasClass('old')) && !($(this).hasClass('new'))) {
                            $(this).addClass('selected');
                        }
                    });
                }
            /*}*/
        });
    };

    $(elementSelector).on('changeMonth', orderMonth);
    $(elementSelector).on('changeDate', function(e) { selectDateCallback(e); });

}
