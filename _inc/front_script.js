console.log('front aetwp!');
const eventsListContainer = document.querySelector('#events_list');

function updateEventsList(events) {
    // const events = loadEvents(month);
    // clear eventsListContainer
    while (eventsListContainer.firstChild) {
        eventsListContainer.removeChild(eventsListContainer.firstChild);
    }

    const eventsList = document.createElement('ul');

    events.forEach((event) => {
        // "yy-mm-dd" => "dd.mm.yy"
        // const date = Date(event.date);
        let date_str = event.date;
        const parts = date_str.split('-');
        // console.log(parts);
        date_str = parts[2]+'.'+parts[1]+'.'+parts[0];

        const markup = `
        <li>
            <p><span class="date_str">${date_str}</span> <a href="${event.permalink}">${event.title}</a></p>
            <p class="description_str">${event.description}</p>
        </li>
        `;

        eventsList.insertAdjacentHTML('beforeend', markup);
    });

    eventsListContainer.appendChild(eventsList);
}

jQuery(document).ready(function($) {
    const datepicker = $('#datepicker');
    
    function loadEvents(month, update=false) {
        console.log(month);
        let events = [];

        $.ajax({
            // method: 'GET',
            url:    myajax.url,
            data:   { action: 'get_events', month: month },
    
            success: function(response){
                events = jQuery.parseJSON(response);
                console.log(events);
                // if (init) init(events);
                if (update) updateEventsList(events);
            } 
        });

        return events;
    }

    function init() {
        // const events = loadEvents(0);
        $.ajax({
            // method: 'GET',
            url:    myajax.url,
            data:   { action: 'get_events', month: 0 },
    
            success: function(response){
                const events = jQuery.parseJSON(response);
                
                datepicker.datepicker({
                    onChangeMonthYear: function(year, month, inst) {
                        loadEvents(month, true);
                    },
                    beforeShowDay: function(d) {
                        const year = d.getFullYear(),
                        month = ("0" + (d.getMonth() + 1)).slice(-2),
                        day = ("0" + (d.getDate())).slice(-2);
                        const iso8601_date = year + '-' + month + '-' + day;
                        let tooltip = '';
                        // console.log(events);
                        events.forEach(event => {
                            if (event.date == iso8601_date)
                            {
                                tooltip += event.title + ' ' + event.description + '\n';
                            }
                        });
            
                        return [true, tooltip ? 'is_event' : '', tooltip];
                    }
                });
            
                const curDate = datepicker.datepicker("getDate");
                const month = curDate.getMonth() + 1;
                loadEvents(month, true);
            } 
        });
    }

    // loadEvents(0);
    init();

    // var change = 0;
    // $('#datepicker').onChangeMonthYear() {
    //     $('#month').text('change #'+(++change));
    //     console.log('change '+change);
    // };
});
