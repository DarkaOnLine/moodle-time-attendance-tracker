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


defined('MOODLE_INTERNAL') || die;

if (!defined('REPORT_LOG_MAX_DISPLAY')) {
    define('REPORT_LOG_MAX_DISPLAY', 150); // days
}

require_once('helpers.php');

function block_attendance_tracker_print_log($course_id, $user_id, $date_from, $date_to){

    global $DB;

    $sql = "
        SELECT t.*, c.fullname AS course, l.name AS lesson, q.name AS quiz
        FROM {block_attendance_tracker} AS t
        LEFT JOIN {course} AS c ON c.id = t.course_id
        LEFT JOIN {lesson} AS l ON l.id = t.lesson_id
        LEFT JOIN {quiz} AS q ON q.id = t.quiz_id
        WHERE course_id = :course_id AND
        user_id = :user_id AND
        date >= :date_from AND
        date <= :date_to
        ORDER BY c.fullname, t.date
    ";

    $records = $DB->get_records_sql($sql,array(
        'course_id' => $course_id,
        'user_id' => $user_id,
        'date_from' => date('Y-m-d 00:00:00',$date_from),
        'date_to' => date('Y-m-d 23:59:59',$date_to),
    ));

    include dirname(__FILE__).'/../tpl/report.phtml';
}


function block_attendance_tracker_report_log_print_selector_form($course_id = 0, $selecteduser = 0, $datefrom = null, $dateto = null, $logformat='showashtml') {

    global $USER, $CFG, $DB, $OUTPUT, $SESSION;

    $helper = new attendance_tracker_helpers();

    $context = $sitecontext = context_system::instance();

    // Get all the possible users
    $users = array();

    // Define limitfrom and limitnum for queries below
    // If $showusers is enabled... don't apply limitfrom and limitnum
    $limitfrom = empty($showusers) ? 0 : '';
    $limitnum  = empty($showusers) ? COURSE_MAX_USERS_PER_DROPDOWN + 1 : '';


    // this may be a lot of users :-(
    $courseusers = $DB->get_records('user', array('deleted'=>0), 'lastaccess DESC', 'id, ' . get_all_user_name_fields(true),
        $limitfrom, $limitnum);


    if ($courseusers) {
        foreach ($courseusers as $courseuser) {
            $users[$courseuser->id] = fullname($courseuser, has_capability('moodle/site:viewfullnames', $context));
        }
    }



    $courses = array();
    $sql = "SELECT DISTINCT id AS course, fullname AS coursename FROM {course} WHERE visible = 1 AND format != 'site'";
    if ($ccc = $DB->get_records_sql($sql)) {
        foreach ($ccc as $cc) {
            $courses["$cc->course"] = $cc->coursename;
        }
    }

    asort($courses);
    asort($users);

    // Prepare the list of action options.
    $actions = array(
        'view' => get_string('view'),
        'add' => get_string('add'),
        'update' => get_string('update'),
        'delete' => get_string('delete'),
        '-view' => get_string('allchanges')
    );

    $datefrom = $datefrom ?: strtotime('-1 year');

    echo "<form class=\"logselectform\" action=\"$CFG->wwwroot/blocks/attendance_tracker/index.php\" method=\"get\">\n";
    echo "<div>\n";

    echo html_writer::label(get_string('selectacoursesite'), 'menuhost_course', false, array('class' => 'accesshide'));
    echo "<sup style='color:red'>*</sup>".html_writer::select($courses, "course_id", $course_id);

    echo html_writer::label(get_string('participantslist'), 'menuuser', false, array('class' => 'accesshide'));
    echo "<sup style='color:red'>*</sup>".html_writer::select($users, "user_id", $selecteduser);

    $helper->calendar($datefrom, $dateto)->display();

    echo '</div>';
    echo '</form>';

}