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
    define('REPORT_LOG_MAX_DISPLAY', 150); // Days.
}

require_once('helpers.php');

/**
 * Show log
 *
 * @param $courseid
 * @param $userid
 * @param $datefrom
 * @param $dateto
 */
function block_attendance_tracker_print_log($courseid, $userid, $datefrom, $dateto) {

    global $DB;

    $sql = "
        SELECT
          {block_attendance_tracker}.*,
          {course}.fullname AS course,
          {lesson}.name AS lesson,
          {quiz}.name AS quiz
        FROM {block_attendance_tracker}
        LEFT JOIN {course} ON {course}.id = {block_attendance_tracker}.course_id
        LEFT JOIN {lesson} ON {lesson}.id = {block_attendance_tracker}.lesson_id
        LEFT JOIN {quiz} ON {quiz}.id = {block_attendance_tracker}.quiz_id
        WHERE course_id = :course_id AND
        user_id = :user_id AND
        date >= :date_from AND
        date <= :date_to
        ORDER BY {course}.fullname, {block_attendance_tracker}.date
    ";

    $records = $DB->get_records_sql($sql, array(
        'course_id' => $courseid,
        'user_id' => $userid,
        'date_from' => date('Y-m-d 00:00:00', $datefrom),
        'date_to' => date('Y-m-d 23:59:59', $dateto),
    ));

    include(dirname(__FILE__).'/../tpl/report.phtml');
}

/**
 * Get log for view
 *
 * @param int $courseid
 * @param int $selecteduser
 * @param null $datefrom
 * @param null $dateto
 * @throws coding_exception
 * @throws dml_exception
 */
function block_attendance_tracker_report_log_print_selector_form(
    $courseid = 0, $selecteduser = 0, $datefrom = null, $dateto = null) {

    global $CFG, $DB;

    $helper = new block_attendance_tracker_helpers();

    $context = $sitecontext = context_system::instance();

    // Get all the possible users.
    $users = array();

    // Define limitfrom and limitnum for queries below.
    // If $showusers is enabled... don't apply limitfrom and limitnum.
    $limitfrom = empty($showusers) ? 0 : '';
    $limitnum  = empty($showusers) ? COURSE_MAX_USERS_PER_DROPDOWN + 1 : '';

    // This may be a lot of users :-(.
    $courseusers = $DB->get_records(
        'user', array('deleted' => 0),
        'lastaccess DESC', 'id, ' . get_all_user_name_fields(true),
        $limitfrom, $limitnum
    );

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

    $datefrom = $datefrom ?: strtotime('-1 year');

    echo "<form class=\"logselectform\" action=\"$CFG->wwwroot/blocks/attendance_tracker/index.php\" method=\"get\">\n";
    echo "<div>\n";

    echo html_writer::label(get_string('selectacoursesite'), 'menuhost_course', false, array('class' => 'accesshide'));
    echo "<sup style='color:red'>*</sup>".html_writer::select($courses, "course_id", $courseid);

    echo html_writer::label(get_string('participantslist'), 'menuuser', false, array('class' => 'accesshide'));
    echo "<sup style='color:red'>*</sup>".html_writer::select($users, "user_id", $selecteduser);

    $helper->calendar($datefrom, $dateto)->display();

    echo '</div>';
    echo '</form>';

}