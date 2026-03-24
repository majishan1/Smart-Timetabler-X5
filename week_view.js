document.getElementById('next_week').addEventListener('click', function() {new_week(1)});
document.getElementById('today').addEventListener('click', function() {jump_today()});
document.getElementById('prev_week').addEventListener('click', function() {new_week(-1)});


function jump_today() {
    week_offset = 0;
    new_week(0);
}

async function new_week(offset_change) {
    week_offset += offset_change;

    let week_items = await get_week_items();
    if (!week_items) return;
    let week_events = week_items[0];
    let week_tasks = week_items[1];

    remove_events();
    update_tasks(week_events);
    update_tasks(week_tasks);


    handle_collisions(check_collisions(week_events.concat(week_tasks)));

    remove_date_bar();
    set_date_bar();
    find_month();
    cur_time_shown();
}

async function get_week_items() {
    let week_start = get_start_of_week();

    let week_end = new Date(week_start);
    week_end.setDate(week_start.getDate() + 6);
    week_end.setHours(23, 59, 59);

    try {
        let response = await fetch(`get_week_items.php?start=${dt_to_SQL_format(week_start)}&end=${dt_to_SQL_format(week_end)}`)
        let data = await response.json();

        if (data.error) {
            console.error("Server Error:", data.error);
            return;
        }

        let week_events = data.events;
        week_events.forEach(event => {
            event.start_time = new Date(event.start_time.replace(' ', 'T'));
            event.tskType = 'event';
        });

        let week_tasks = data.toDo;
        week_tasks.forEach(task => {
            task.start_time = new Date(task.deadline.replace(' ', 'T'));
            task.tskType = 'task';
            delete task.deadline;
        });

        let week_assignments = data.assignments;
        week_assignments.forEach(assignment => {
            assignment.start_time = new Date(assignment.due_date.replace(' ', 'T'));
            assignment.duration = 20;
            assignment.tskType = 'assignment';
            delete assignment.due_date;
        })

        return [week_events, week_tasks, week_assignments];
    }
    catch (err) {
        console.error("AJAX request failed: ", err);
        return;
    }
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
    document.querySelectorAll('.event_block, .extended_block').forEach(event => event.remove());
}

function update_tasks(events) {
    for (var i = 0; i < events.length; i++) {
        let event = events[i];

        let event_dur = parseInt(event.duration);
        let event_time = event.start_time;

        let event_day = event_time.getDay();
        let event_hour = event_time.getHours();
        let event_min = event_time.getMinutes();

        let mins_into_day = event_hour * 60 + event_min;

        let day_col = document.getElementById(String(event_day));

        // For the standard (unselected) event blocks
        let new_event_div = document.createElement('div');
        new_event_div.className = "event_block " + event.type.toUpperCase();
        if (event.tskType === 'event') {
            new_event_div.id = "ev" + event.EventID;
            new_event_div.textContent = event.module;
        }
        else if (event.tskType === 'task') {
            new_event_div.id = "tk" + event.taskID;
            new_event_div.textContent = event.title;
            new_event_div.style.backgroundColor = `color-mix(in srgb , ${event.color} 30%, white 70%)`;
            new_event_div.style.borderColor = `color-mix(in srgb , ${event.color} 20%, white 80%)`;
            new_event_div.style.borderLeftColor = event.color;
        }

        new_event_div.style.top = mins_into_day + "px"
        new_event_div.style.height = event_dur + "px"
        new_event_div.addEventListener('click', function() {show_extended(new_event_div.id)});

        day_col.appendChild(new_event_div);

        //For extended (selected) event blocks
        let extended_event_div = document.createElement('div');

        let min_hour = {hour: '2-digit', minute: '2-digit'};
        let end_time = new Date((event_time.getTime() + event_dur * 60000));
        let start_t = event_time.toLocaleTimeString([], min_hour);
        let end_t = end_time.toLocaleTimeString([], min_hour);


        extended_event_div.className = event.type.toUpperCase() + " extended_block";
        if (event.tskType === 'event') {

            extended_event_div.id = "eev" + event.EventID;        
            extended_event_div.innerHTML = `
            <div class="ext_header">
                <h3>${escapeHTML(event.module)}</h3>
                <button type="button" class="close_button" onclick="hide_extended('${escapeHTML('ev' + event.EventID)}')">&times;</button>
            </div>
            <div class="ext_body">
                <p><strong>Type: </strong>${escapeHTML(event.type)}</p>
                <p><strong>Time: </strong>${escapeHTML(start_t)} - ${escapeHTML(end_t)}</p>
                <p><strong>Location: </strong>${escapeHTML(event.location || 'TBC')}</p>
                <p><strong>Staff: </strong>${escapeHTML(event.staff || 'TBC')}</p>
            </div>`
        }

        else if (event.tskType === 'task') {
            extended_event_div.id = "etk" + event.taskID;
            extended_event_div.innerHTML = `
            <div class="ext_header">
                <h3>${escapeHTML(event.title)}</h3>
                <button type="button" class="close_button" onclick="hide_extended('${escapeHTML('tk' + event.taskID)}')">&times;</button>
            </div>
            <div class="ext_body">
                <p><strong>Type: </strong>${escapeHTML(event.type)}</p>
                <p><strong>Description: </strong>${escapeHTML(event.description)}</p>
                <p><strong>Time: </strong>${escapeHTML(start_t)} - ${escapeHTML(end_t)}</p>
            </div>`
            extended_event_div.style.backgroundColor = `color-mix(in srgb , ${event.color} 30%, white 70%)`;
            extended_event_div.style.borderColor = `color-mix(in srgb , ${event.color} 20%, white 80%)`;
            extended_event_div.style.borderLeftColor = event.color;
        }

        extended_event_div.style.visibility = "hidden";
        document.body.appendChild(extended_event_div);
    }
}
 

function check_collisions(events) {
    let colliding_clusters = [];
    let cur_cluster = [];
    let cur_cluster_end = 0;

    events.sort((a, b) => a.start_time - b.start_time);

    for (var i = 0; i < events.length; i++) {

        let ev_s = events[i].start_time.getTime();
        let ev_e = ev_s + events[i].duration * 60000;

        // if no current collision
        if (cur_cluster.length === 0) {
            cur_cluster.push(events[i]);
            cur_cluster_end = ev_e;
        }
        else {
            // Is there is a collision?
            if (ev_s < cur_cluster_end) {
                cur_cluster.push(events[i]);

                if (ev_e > cur_cluster_end) {
                    cur_cluster_end = ev_e;
                }
            }
            else {
                colliding_clusters.push(cur_cluster);
                cur_cluster = [events[i]];
                cur_cluster_end = ev_e;
            }
        }
    }
    if (cur_cluster.length > 0) colliding_clusters.push(cur_cluster);
    return colliding_clusters;
}

function handle_collisions(collisions_arr) {
    for (var i = 0; i < collisions_arr.length; i++) {
        let no_colliding = collisions_arr[i].length;
        let lanes = [];

        for (var j = 0; j < no_colliding; j++) {
            let event = collisions_arr[i][j];
            let ev_start = event.start_time;
            let ev_height = new Date(ev_start.getTime() + event.duration * 60000);
            let ev_lane = -1;

            for (var k = 0; k < lanes.length; k++) {
                if (ev_start >= lanes[k]) {
                    ev_lane = k;
                    break;
                }
            }
            if (ev_lane === -1) {
                ev_lane = lanes.length;
                lanes.push(ev_height);
            }
            else {
                lanes[ev_lane] = ev_height;
            }
            event.lane = ev_lane;
        }

        let total_lanes = lanes.length;
        let ev_widths = 97.5 / total_lanes;
        for (var l = 0; l < collisions_arr[i].length; l++) {
            let ev = collisions_arr[i][l];
            let ev_id = (ev.tskType === 'task')? 'tk' + ev.taskID: 'ev' + ev.EventID;
            let ev_block = document.getElementById(ev_id);
            if (ev_block) {
                ev_block.style.width = ev_widths + "%";
                ev_block.style.left = (1.25 + (ev.lane * ev_widths)) + "%";
            }
        }
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
    let time_line = document.getElementById("cur_time");
    let marker1 = document.getElementById('marker1');
    let marker2 = document.getElementById('marker2');
    if (time_line !== null) {
        if (week_offset === 0) {
            time_line.style.visibility = "visible";
            marker1.style.visibility = "visible";
            marker2.style.visibility = "visible";
            time_line.style.top = get_line_height()[0] + "px";
            marker1.style.top = (get_line_height()[0]) + "px";
            marker2.style.top = (get_line_height()[0]) + "px";
        }
        else {
            time_line.style.visibility = "hidden";
            marker1.style.visibility = "hidden";
            marker2.style.visibility = "hidden";
        }
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
    let marker1 = document.createElement('div');
    let marker2 = document.createElement('div');

    time_line.className = "cur_time_line";
    marker1.className = "time_marker";
    marker2.className = "time_marker";

    time_line.id = "cur_time";
    marker1.id = "marker1";
    marker2.id = "marker2";

    time_line.style.top = line_height + "px";
    marker1.style.top = line_height + "px";
    marker2.style.top = line_height + "px";

    marker1.style.left = 100/52 * (3 + 7 * (today - 1)) + "%";
    marker2.style.left = 100/52 * (3 + 7 * today) + "%";


    week_box.appendChild(marker1);
    week_box.appendChild(marker2);
    week_box.appendChild(time_line);
}

function show_extended(id) {
    let extended_boxes = document.getElementsByClassName('extended_block');
    for (var i = 0; i < extended_boxes.length; i++) {
        extended_boxes[i].style.visibility = "hidden";
    }
    let extended_box  = document.getElementById('e' + id);
    extended_box.style.visibility = "visible";
    document.querySelector('.calendarContainer').style.filter = "blur(10px)";
}

function hide_extended(id) {
    let extended_box = document.getElementById('e' + id);
    extended_box.style.visibility = "hidden";
    document.querySelector('.calendarContainer').style.filter = "blur(0px)";
}

function escapeHTML(text) {
    let map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }
    return String(text).replace(/[&<>"']/g, function(m) { return map[m];});
}

function dt_to_SQL_format(date) {
    // MySQL doesn't accept .js date object, so need to convert to ISO format
    const pad = (num) => num.toString().padStart(2, '0');
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())} ${pad(date.getHours())}:${pad(date.getMinutes())}:${pad(date.getSeconds())}`;
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