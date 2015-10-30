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
 * @copyright  Darius Matulionis | http://matulionis.lt | darius@matulionis.lt
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
global $CFG, $PAGE, $OUTPUT, $DB;

require_once('lib/helpers.php');
require_once('lib/local.php');
require_once($CFG->libdir.'/adminlib.php');

$userid = optional_param('user_id', 0, PARAM_INT);
$courseid = optional_param('course_id', '', PARAM_INT);
$datefromget = optional_param_array('datefrom', 0, PARAM_INT);

if (is_string($datefromget)) {
    $datefrom = $datefromget;
} else {
    $datefromyear = (int) $datefromget['year'];
    $datefrommonth = (int) $datefromget['month'];
    $datefromday = (int) $datefromget['day'];
    $datefrom = strtotime("$datefromyear-$datefrommonth-$datefromday 00:00:00");
}

$datetoget = optional_param_array('dateto', 0, PARAM_INT);
if (is_string($datetoget)) {
    $dateto = $datetoget;
} else {
    $datetoyear = (int) $datetoget['year'];
    $datetomonth = (int) $datetoget['month'];
    $datetoday = (int) $datetoget['day'];
    $dateto = strtotime("$datetoyear-$datetomonth-$datetoday 23:59:00");
}

// Which page to show.
$page = optional_param('page', '0', PARAM_INT);
// How many per page.
$perpage = optional_param('perpage', '100', PARAM_INT);

$params = array();
if ($courseid !== 0) {
    $params['course_id'] = $courseid;
}
if ($userid !== 0) {
    $params['user'] = $userid;
}
if ($datefrom !== 0) {
    $params['datefrom'] = $datefrom;
}
if ($dateto !== 0) {
    $params['dateto'] = $dateto;
}
if ($page !== '0') {
    $params['page'] = $page;
}
if ($perpage !== '100') {
    $params['perpage'] = $perpage;
}

$PAGE->set_url('/block/attendance_tracker/index.php', $params);
$PAGE->set_pagelayout('report');

if (!empty($page)) {
    $strlogs = get_string('logs').': '.get_string('page', 'report_log', $page + 1);
} else {
    $strlogs = get_string('logs');
}
if ($userid && $courseid) {
    admin_externalpage_setup('reportlog');
    $course = $DB->get_record('course', array('id' => $courseid));
    $user = $DB->get_record('user', array('id' => $userid));

    $userinfo = get_string('allparticipants');
    $dateinfo = get_string('alldays');

    if ($user) {
        $userinfo = fullname($user, true);
    }
    if ($datefrom) {
        $datefrominfo = userdate($datefrom, get_string('strftimedaydate'));
    }
    if ($dateto) {
        $datetoinfo = userdate($dateto, get_string('strftimedaydate'));
    }

    $PAGE->set_title($course->shortname.': '.$strlogs);
    $PAGE->set_heading($course->fullname);
    $PAGE->navbar->add('Attendance journal report');
    echo $OUTPUT->header();

    echo $OUTPUT->heading(format_string($course->fullname).": $userinfo");

    block_attendance_tracker_report_log_print_selector_form($courseid, $userid, $datefrom, $dateto);
    block_attendance_tracker_print_log($courseid, $userid, $datefrom, $dateto);
} else {
    admin_externalpage_setup('reportlog', '', null, '', array('pagelayout' => 'report'));
    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string('chooselogs').':');

    block_attendance_tracker_report_log_print_selector_form();
}

echo $OUTPUT->footer();
