document.getElementById('next_week').addEventListener('click', function() {new_week(1)});
document.getElementById('today').addEventListener('click', function() {jump_today()});
document.getElementById('prev_week').addEventListener('click', function() {new_week(-1)});


function jump_today() {
    week_offset = 0;
    new_week(0);
}

function new_week(offset_change) {
    week_offset += offset_change;
    let week_events = get_week_events();
    remove_events();
    update_events(week_events);
    remove_date_bar();
    set_date_bar();
    find_month();
    cur_time_shown();
}

function get_week_events() {
    let week_start = get_start_of_week();

    let week_end = new Date(week_start);
    week_end.setDate(week_start.getDate() + 6);
    week_end.setHours(23, 59, 59);

    let selected_events = [];

    for (var i = 0; i < events.length; i++) {
        let event = events[i];
        let ev_date = new Date(event.start_time.replace(' ', 'T'));
        if (ev_date >= week_start && ev_date <= week_end) {
            selected_events.push(event);
        }
    }
    return selected_events;
}

function get_start_of_week() {
    let target_date = new Date();
    target_date.setDate(target_date.getDate() + week_offset * 7);
    let day_of_target = target_date.getDay();

    let week_start = new Date(target_date);
    week_start.setDate(target_date.getDate() - day_of_target);
    week_start.setHours(0, 0, 0);
    return week_start;
}

function remove_events() {
    let event_blocks = document.getElementsByClassName('event_block');
    while (event_blocks.length > 0) {
        event_blocks[0].parentNode.removeChild(event_blocks[0]);
    }
}

function update_events(events) {
    for (var i = 0; i < events.length; i++) {
        let event = events[i];

        let event_dur = parseInt(event.duration);
        let event_time = new Date(event.start_time.replace(' ', 'T'));

        let event_day = event_time.getDay();
        let event_hour = event_time.getHours();
        let event_min = event_time.getMinutes();

        let mins_into_day = event_hour * 60 + event_min;

        let day_col = document.getElementById(String(event_day));

        let new_event_div = document.createElement('div');
        new_event_div.className = "event_block" + " " + event.type.toUpperCase();
        new_event_div.textContent = event.module;
        new_event_div.style.top = mins_into_day + "px"
        new_event_div.style.height = event_dur + "px"

        

        day_col.appendChild(new_event_div);

    }
}

function remove_date_bar() {
    let day_container = document.getElementById('-1')
    while (day_container.children.length > 1) {
        day_container.removeChild(day_container.children[1]);
    }
}

function set_date_bar() {
    let this_week_s = get_start_of_week();
    let days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    let titles_div = document.getElementById('-1');

    for (var i = 0; i < days.length; i++) {
        let name = days[i];
        let name_div = document.createElement('div');
        name_div.className = "day_name";
        name_div.id = "d" + i;
        name_div.innerHTML = name + "<br>" + this_week_s.getDate();
        this_week_s.setDate(this_week_s.getDate() + 1);
        titles_div.appendChild(name_div);
    }

    if (week_offset === 0) {
        let today = new Date();
        let day = document.getElementById('d' + today.getDay());
        day.className += ' today'
    }
}

function find_month() {
    let week_start = get_start_of_week();
    let month_container = document.getElementById('month');
    month_container.textContent = week_start.toLocaleString('default', {month: 'long'});
}

function cur_time_shown() {
    
    if (week_offset === 0) {
        let time_line = document.getElementById("cur_time");
        if (time_line !== null) {
            time_line.style.visibility = "visible";
            time_line.style.top = get_line_height()[0] + "px";
        }
    }
    else {
        let time_line = document.getElementById("cur_time");
        time_line.style.visibility = "hidden";
    }
}

function get_line_height() {
    let date_now = new Date();
    let today = date_now.getDay() + 1;
    return [date_now.getHours() * 60 + date_now.getMinutes(), today];
}

function calc_and_show_cur_time() {
    let time_data = get_line_height();
    let line_height = time_data[0];
    let today = time_data[1];

    let week_box = document.getElementById('WeekID');
    let time_line = document.createElement('hr');

    time_line.className = "cur_time_line";
    time_line.id = "cur_time";
    time_line.style.top = line_height + "px";
    time_line.style.width = 100/52 * (3 + 7 * today) + "%";

    week_box.appendChild(time_line);
}

// Shows the days of the week and dates when program loaded
jump_today();
calc_and_show_cur_time();

let time_col = document.getElementById('7');
for (var i = 0; i < 24; i++) {
    let time = document.createElement('div')
    time.className = "time";
    time.textContent = i > 9 ? i + ":00": "0" + i + ":00";
    time_col.appendChild(time);
}

// Scroll to show the current time of day
let today = new Date();
let cal_box = document.querySelector('.calendarContainer');
let line_height = today.getHours() * 60 + today.getMinutes();
cal_box.scrollTo(0, ((line_height > 60)? line_height - 60: line_height));