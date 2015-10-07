<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @package    block_attendance_tracker
 * @copyright  Darius Matulionis | http://matulionis.lt | darius@matulionis.lt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;

require_once('lib/helpers.php');
require_once('lib/local.php');
require_once($CFG->libdir.'/adminlib.php');

$user_id        = optional_param('user_id', 0, PARAM_INT); // User to display
$course_id     = optional_param('course_id', '', PARAM_INT);
$date_from_get = optional_param_array('datefrom', 0, PARAM_INT);

if (is_string($date_from_get)) {
    $date_from = $date_from_get;
} else {
    $date_from_year    = (int)$date_from_get['year'];
    $date_from_month   = (int)$date_from_get['month'];
    $date_from_day     = (int)$date_from_get['day'];
    $date_from = strtotime("$date_from_year-$date_from_month-$date_from_day 00:00:00");
}

$date_to_get = optional_param_array('dateto', 0, PARAM_INT);
if (is_string($date_to_get)) {
    $date_to = $date_to_get;
} else {
    $date_to_year    = (int)$date_to_get['year'];
    $date_to_month   = (int)$date_to_get['month'];
    $date_to_day     = (int)$date_to_get['day'];
    $date_to = strtotime("$date_to_year-$date_to_month-$date_to_day 23:59:00");
}
$page        = optional_param('page', '0', PARAM_INT);     // which page to show
$per_page     = optional_param('perpage', '100', PARAM_INT); // how many per page

$params = array();
if ($course_id !== 0) {
    $params['course_id'] = $course_id;
}
if ($user_id !== 0) {
    $params['user'] = $user_id;
}
if ($date_from !== 0) {
    $params['datefrom'] = $date_from;
}
if ($date_to !== 0) {
    $params['dateto'] = $date_to;
}
if ($page !== '0') {
    $params['page'] = $page;
}
if ($per_page !== '100') {
    $params['perpage'] = $per_page;
}

$PAGE->set_url('/block/attendance_tracker/index.php', $params);
$PAGE->set_pagelayout('report');


if (!empty($page)) {
    $str_logs = get_string('logs'). ": ". get_string('page', 'report_log', $page + 1);
} else {
    $str_logs = get_string('logs');
}
if($user_id && $course_id){
    admin_externalpage_setup('reportlog');
    $course = $DB->get_record('course',array('id'=>$course_id));
    $user = $DB->get_record('user',array('id'=>$user_id));

    $user_info = get_string('allparticipants');
    $date_info = get_string('alldays');

    if ($user) {
        $user_info = fullname($user, true);
    }
    if ($date_from) {
        $date_from_info = userdate($date_from, get_string('strftimedaydate'));
    }
    if ($date_to) {
        $date_to_info = userdate($date_to, get_string('strftimedaydate'));
    }


    $PAGE->set_title($course->shortname .': '. $str_logs);
    $PAGE->set_heading($course->fullname);
    $PAGE->navbar->add("Attendance journal report");
    echo $OUTPUT->header();

    echo $OUTPUT->heading(format_string($course->fullname) . ": $user_info");

    block_attendance_tracker_report_log_print_selector_form($course_id, $user_id, $date_from, $date_to);
    block_attendance_tracker_print_log($course_id, $user_id, $date_from, $date_to);

}else{
    admin_externalpage_setup('reportlog', '', null, '', array('pagelayout' => 'report'));
    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string('chooselogs') .':');

    block_attendance_tracker_report_log_print_selector_form();
}

echo $OUTPUT->footer();