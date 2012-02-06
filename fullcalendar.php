<?php
include_once("includes/config.php");
$propID = isset($_GET['id']) ? $_GET['id'] : '6'; // just for test
$sql = " SELECT c.curcy_symbol FROM ".DBCURNCY." c  INNER JOIN ".DBPRICING." p ON p.currency_id = c.curcy_id
         WHERE p.property_id =".(int)$propID;
$symbol = $db->queryUniqueValue($sql);
//__print($_SESSION);
?>
<html>
<head>
    <link rel='stylesheet' type='text/css' href='css/fullcalendar.css' />
    <link rel='stylesheet' type='text/css' href='css/dialog.css' />
    <link rel="stylesheet" type="text/css" href="css/ui-lightness/jquery-ui-1.8.16.custom.css" />
    <script src='scripts/jquery-1.7.min.js'></script>
    <script src='scripts/functions.js'></script>
    <script src="scripts/jquery-ui-1.8.17.custom.min.js"></script>
    <script src="scripts/jquery.ui.draggable.js"></script>
    <script src='scripts/fullcalendar.min.js'></script>
    <style>
    .container {
            height:500px;
            width:500px
        }
    .dialogformfield {margin-bottom: 8px;}
    .bookedClass { background-color:#FFCCCC;}
    </style>
    <script>
        $(function(){

         var propID = getParameterByName('id');
         var ownerID = '<?php echo $_SESSION['userId'];?>'; // property owner's user ID retrieve from session variables
         var dateArr = []; // global variable to save the displayed days dates
         var currentDate = new Date(); // today's Date
         // here we set the ajax paramaets before so that we need not to write each and every time
         $.ajaxSetup({
                   type : 'POST',
                   cache : false,
                   url : './f_new_event.php/',
                   dataType : 'json'
         });
         var addEvent = function (start, end) {
                 var title = $('#edited_title')[0].value;
                 if (title) {
                     var startDate = Date.parse(start) / 1000;
                     var endDate = Date.parse(end) / 1000;
                     //console.log('startdate:'+startDate+'End date:'+endDate);
                     if (startDate <= endDate) {
                         // we need to change format of dates according to MySQL date format
                         var startDateString = $.fullCalendar.formatDate(start, 'yyyy-MM-dd');
                         var endDateString = $.fullCalendar.formatDate(end, 'yyyy-MM-dd');
                         $.ajax({
                             data: {
                                 action :'add',
                                 startDate: startDateString,
                                 endDate: endDateString,
                                 eventTitle: title,
                                 propID: propID,
                                 userID: ownerID
                             },
                             success: function (result) {
                                 //console.log(result.event);
                                 result.event.disableDragging = true;
                                 result.event.disableResizing = true;
                                 $('#myCalendar').fullCalendar('renderEvent', result.event, false);
                                 // this method can be used if we want to fetch all events again
                                 //$('#myCalendar').fullCalendar('refetchEvents');
                             }
                         }); // end of ajax
                     } else {
                         // btw this will never happens :)
                         alert('dates Mismatch');
                     }
                 }
             }

         var updateEvent = function (event) {
                 var title = $('#edited_title')[0].value;
                 if ($.trim(title) !== null && $.trim(title) !== '') {
                     event.title = $.trim(title);
                     var startDate = Date.parse(event.start) / 1000;
                     var endDate = event.end !== null ? Date.parse(event.end) / 1000 : startDate;
                     //event.allDay = 1;
                     if (startDate <= endDate) {
                         $.ajax({
                             data: {
                                 action : 'edit',
                                 eventId: event.id,
                                 eventTitle: event.title
                             },
                             success: function (result) {
                                 //console.log(result);
                                 result.event.disableDragging = true;
                                 result.event.disableResizing = true;
                                 $('#myCalendar').fullCalendar('updateEvent', result.event);
                                 //$('#myCalendar').fullCalendar('refetchEvents');
                             }
                         });
                     } else {
                         alert('Unable to update :(');
                     }
                 }
             }

         var removeEvent = function (event) {
                 //console.log(event);
                 if (event.id) {
                     $.ajax({
                         data: {
                             action : 'del',
                             eventId: event.id
                         },
                         success: function (result) {
                             //console.log(result);
                         }
                     });
                     $('#myCalendar').fullCalendar('removeEvents', event.id);
                     //$('#myCalendar').fullCalendar('refetchEvents');
                 } else {
                     alert('Event id not found');
                 }
             }
             // paints the days cells
         function onRenderCalDay(event, element, view) {
               // console.dir(event);
             if (event.end == null) event.end = event.start;
             var daySlots = view.element.find('td');
             for (var i in dateArr) {
                 aSlot = $(daySlots[i]);
                 var est = event.start.getTime();
                 var eet = event.end.getTime();
                 var mydate = dateArr[i].getTime();
                 if ((est <= mydate) && (mydate <= eet)) {
                     if (aSlot != null) {
                         aSlot.addClass("bookedClass"); // colorize the day cell through custom css class
                     }
                 }
             }
         }

         // this function builds an array with the displayed days
         function viewCalDisplay(view) {
             var $calE = $('#myCalendar');
             var today = $calE.fullCalendar('getDate');
             var cMonth = today.getMonth();
             var cYear = today.getFullYear();
             //console.log('cMonth'+cMonth+'cYear'+cYear);
             var oneDay, oneMonth, oneYear, oneDate;
             var $cal_slots = $(view.element.find('.fc-day-number'));
             if ($cal_slots !== null) {
                 $cal_slots.each(function () {
                     oneDay = parseInt($(this).text(), 10);
                     oneYear = parseInt(cYear, 10);
                     //check if it is another month date
                     if ($(this).parents('td').hasClass('fc-other-month')) {

                         if (oneDay > 15) {
                             oneMonth = parseInt(cMonth, 10) - 1;
                             if (cMonth == 0) {
                                 oneYear = oneYear - 1
                             }
                             //console.log('\n Prev Month:=> oneYear:'+oneYear+'oneMonth:'+oneMonth+'oneDay:'+oneDay);
                             oneDate = new Date(oneYear, oneMonth, oneDay);
                             dateArr.push(oneDate);
                         } else { //belong to the next month
                             //console.log($calE.fullCalendar('getDate'));
                             oneMonth = parseInt(cMonth, 10) + 1;
                             if (cMonth == 11) {
                                 oneYear = oneYear + 1
                             }
                             //console.log('\n Next Month :=> oneYear:'+oneYear+'oneMonth:'+oneMonth+'oneDay:'+oneDay);
                             oneDate = new Date(oneYear, oneMonth, oneDay);
                             dateArr.push(oneDate);
                         }
                     } else {
                         oneMonth = parseInt(cMonth, 10);
                         //console.log('\n otherwise :=> oneYear:'+oneYear+'oneMonth:'+oneMonth+'oneDay:'+oneDay);
                         oneDate = new Date(oneYear, oneMonth, oneDay);
                         dateArr.push(oneDate);
                     }
                 });
             }
             $(".bookedClass").removeClass("bookedClass"); // Remove all booked class from element
             var events = view.calendar.clientEvents(function (event) {
                 //we need only events of current month
                 return event.start.getMonth() == view.start.getMonth();
             });
             for (i = 0; events.length > i; i++) {
                 var event = events[i];
                 // We need only days of current month. We select <td> of calender table (by class .ui-widget-content)
                 var elements = $(".ui-widget-content:not(.fc-other-month)").filter(function () {
                     //We try to find day number witch corresponds to the event date
                     return (event.end == null && $(this).find(".fc-day-number").text() == event.start.getDate()) //If we have 1 day event
                     ||
                     (event.end != null && $(this).find(".fc-day-number").text() >= event.start.getDate() // If we have many day event
                     &&
                     $(this).find(".fc-day-number").text() <= event.end.getDate())
                 });
                 elements.addClass("bookedClass"); //Only for this <td>
             }
         }



         $('#myCalendar').empty();
         var calendar = $('#myCalendar').fullCalendar({
             theme: true,
             defaultView: 'month',
             header: {
                 left: 'prev',
                 center: 'title',
                 right: 'next'
             },
             eventRender: onRenderCalDay,
             viewDisplay: viewCalDisplay,
/*             eventAfterRender: function (event, element, view) {
*               $(element).css({ padding: '2px' });
*
*               // To Hide all events related to previous and next months
*               // If we event ends in previous month or starts in next we dont show it!
*               if(
*                   (event.end == null && (view.start.getMonth() != event.start.getMonth())) //If we have 1 day event
*                 || (event.end != null && (view.start.getMonth() > event.end.getMonth() // If we have many day event
*                   || view.start.getMonth() < event.start.getMonth()))
*
*              ){
*                   $(element).hide();
*              }
*            },
*/
             lazyFetching: true,
             loading: function (bool) {
                 if (bool) $('#loading').show();
                 else $('#loading').hide();
             },
             eventSources: [{
                 url : './f_new_event.php/',
                 type : 'POST',
                 cache : true,
                 data : {
                     action: 'get',
                     propID: propID
                 },
                 error : function () {
                     alert('there was an error while fetching events!');
                 },
                 color : '#F1F003',
                 textColor : '#000'
             }, {
                 url: './new_charges.php',
                 type: 'POST',
                 cache: true,
                 data: {
                     propID: propID
                 },
                 error: function () {
                     alert('there was an error while fetching events!');
                 },
                 color: '#383C3D',
                 textColor: '#A99834'
             }],

             eventClick: function (event) {
                 if (event.editable && currentDate <= event.start) {
                     $("#divInDialog").dialog("destroy");
                     $("#dialog:ui-dialog").dialog("destroy");
                     $("#message_box").show();
                     $('#edited_title')[0].value = event.title;
                     $("#dialog-message").dialog({
                         modal: true,
                         resizeable: false,
                         title: 'Update charge',
                         buttons: {
                             Update: function () {
                                 $(this).dialog("close");
                                 updateEvent(event);
                             },
                             Cancel: function () {
                                 $(this).dialog("close");
                             }
                         }
                     });
                 }
             },
             /*
              *@url :https://github.com/althaus/fullcalendar/blob/devel/demos/selectable.html
              */
             selectable: true,
             selectHelper: true,
             select: function (start, end) {
                 //debugger;
                 var events = calendar.fullCalendar('clientEvents');
                 for (var i = 0; events.length > i; i++) {
                     //If we have 1 day event
                     var est = events[i].start.getTime(); // event start time
                     if (events[i].end != null) var eet = events[i].end.getTime(); // event end time
                     var st = start.getTime(); //start time
                     var et = end.getTime(); // end time
                     if ((events[i].end == null && (est >= st && est <= et)) ||
                     // If we have many days event
                     (events[i].end != null && ((est >= st && est <= et) || (eet >= st && est <= et) || (est <= st && eet >= et)))) {
                         //Information to be shown on clock of any event
                         var fromDate = '<i>Date:</i> ' + $.fullCalendar.formatDate(events[i].start, 'ddd, MMM dd, yyyy') + '.';
                         var toDate = (events[i].end !== events[i].start) ? '<br/><i>Upto:</i> ' + $.fullCalendar.formatDate(events[i].end, 'ddd, MMM dd, yyyy') + '.' : '';
                         if (events[i].className == 'preBook') {
                             var details = events[i].description;
                         }
                         if (events[i].className == 'editable') {
                             var details = ' <br/><i>Special Charge(s):</i> ' + '<?php echo html_entity_decode($symbol)?>' + events[i].title;
                         }
                         var infoBox = fromDate + toDate + details;
                         //console.log(infoBox);
                         $("#divInDialog").dialog("destroy");
                         $("#divInDialog").dialog({
                             position: [100, 170],
                             open: function () {
                                 $('#divInDialog').html(infoBox);
                             }
                         });
                         return false;
                     }
                 }
                 if (currentDate > start) {
                     $("#divInDialog").dialog("destroy");
                     $("#divInDialog").dialog({
                         position: [100, 170],
                         open: function () {
                             $('#divInDialog').html('Selected date is past date');
                         }
                     });
                     return false;
                 } else {
                     // If this time is not busy, for example, we promt to enter Event Title
                     $("#divInDialog").dialog("destroy");
                     $("#dialog:ui-dialog").dialog("destroy");
                     $("#message_box").show();
                     $('#edited_title')[0].value = '';
                     $("#dialog-message").dialog({
                         modal: true,
                         resizable: false,
                         position: [100, 170],
                         title: 'Add charge',
                         buttons: {
                             Save: function () {
                                 $(this).dialog("close");
                                 addEvent(start, end);
                             },
                             Cancel: function () {
                                 $(this).dialog("close");
                             }
                         }
                     });
                 } // end of else
                 calendar.fullCalendar('unselect');
             },

            eventDrop:	function(event ) {
                    $('#myCalendar').fullCalendar('refetchEvents');
            },
             eventMouseover: function (event, jsEvent, view) {
                 //console.log(event.className);
                 if (event.editable && currentDate <= event.start) {
                     var layer = '<div id="events-layer" class="fc-transparent" style="position:absolute; width:100%; height:100%; top:-1px; text-align:right; z-index:100">' + '<a><img src="./images/delete.png" title="delete" width="14" id="delbut' + event.id + '" border="0" style="padding:1px 2px;" /></a>' + '</div>';

                     $(this).append(layer);
                     $("#delbut" + event.id).hide();
                     $("#delbut" + event.id).fadeIn(200);
                     $(document).on('click', '#delbut' + event.id + '', function (e) {
                         e.preventDefault();
                         $("#divInDialog").dialog("destroy");
                         $("#dialog:ui-dialog").dialog("destroy");
                         $("#message_box").hide();
                         $("#dialog-message").dialog({
                             modal: true,
                             resizable: false,
                             position: [100, 170],
                             title: 'Are you sure want to delete this!',
                             buttons: {
                                 Confirm: function () {
                                     $(this).dialog("close");
                                     removeEvent(event);
                                 },
                                 Cancel: function () {
                                     $(this).dialog("close");
                                 }
                             }
                         });
                     });
                 }
             },
             eventMouseout: function () {
                 $("#events-layer").remove();
             }
         });
});
</script>
</head>
<body>
    <div id="dialog-message" title="Special charge(s)" style="display: none;">
	<form>
	<div style="text-align:left;" id="message_box">
            <input type="text" id="edited_title" class="numeric">
        </div>
	</form>
    </div>
    <div id="divInDialog" title="Charge Details"></div>
    <div class="container" >
        <div id="myCalendar"></div>
        <br/><b>Note:</b>
        <?php printf("All charges are in  %s",html_entity_decode($symbol)); ?>
    </div>
</body>
</html>

