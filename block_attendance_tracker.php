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
defined('MOODLE_INTERNAL') || die();

require_once ('lib/helpers.php');

/**
 * Class block_attendance_tracker
 */
class block_attendance_tracker extends block_base {

    /**
     * @var attendance_tracker_helpers
     */
    protected $helper;

    /**
     * @var array
     */
    public $availablemodulesfortracking = array(
        'lesson', 'quiz'
    );

    /**
     * Init plugin
     * @throws coding_exception
     */
    function init() {

        $this->title = get_string('blocktitle', 'block_attendance_tracker');
        $this->helper = new attendance_tracker_helpers();
    }

    /**
     * Inject tracker JS script
     * @throws coding_exception
     */
    function inject_tracker(){
        global $CFG, $USER, $COURSE, $PAGE, $attendancetrackerscriptenebled;

        $pagetype = $PAGE->pagetype;

        if(!$attendancetrackerscriptenebled &&
            $this->helper->blocks_allowed_in_format($pagetype, $this->applicable_formats()) && isloggedin()) {

            $cmid = optional_param('id',0, PARAM_INT);
            if($cmid) {
                $module = $this->helper->get_module_by_cmid($cmid);
            }

            $quizattemptid = optional_param('attempt',0, PARAM_INT);
            $quizid = null;
            if($quizattemptid){
                $quizid = $this->helper->get_quiz_id_by_attempt_id($quizattemptid);
            }

            if( ($module && in_array($module->modname,$this->availablemodulesfortracking) || $quizid ) ) {
                $ispopup = (strpos($_SERVER['SCRIPT_NAME'], 'mod/chat/gui_header_js/') > 0) ? true : false;
                $attendancetrackerupdateurl = $CFG->wwwroot . '/blocks/attendance_tracker/lib/ajax_update_attendance.php';

                $jsparams = array(
                    'user_id' => $USER->id,
                    'course_id' => $COURSE->id,
                    'is_popup' => $ispopup,
                    'url' => $attendancetrackerupdateurl,
                    'lesson_id' => null,
                    'quiz_id' => null,
                    'key' => $USER->sesskey,
                );

                if($module && $module->modname == 'lesson'){
                    $jsparams['lesson_id'] = $module->instance;
                }elseif($quizid){
                    $jsparams['quiz_id'] = $quizid;
                }

                $this->page->requires->js_call_amd('block_attendance_tracker/tracker', 'initialise', $jsparams);

                $attendancetrackerscriptenebled = true;
            }
        }
    }

    /**
     * @return stdClass
     * @throws coding_exception
     */
    function get_content() {
        global $CFG;

        $this->inject_tracker();


        $this->content = new stdClass;
        $context = context_block::instance($this->instance->id);
        if (has_capability('block/attendance_tracker:view', $context)) {
            $url = $CFG->wwwroot.'/blocks/attendance_tracker/index.php';
            $this->content->text = '
            <div id="settingsnav" class="box block_tree_box">
                <ul class="block_tree list">
                    <li class="type_setting">
                        <p class="tree_item leaf">
                            <a href="'.$url.'">'.get_string('link', 'block_attendance_tracker').'</a>
                        </p>
                    </li>
                </ul>
            </div>
            ';
            $this->content->footer = null;
        }

        return $this->content;
    }


    /**
     * @return array
     */
    function applicable_formats() {
        return array(
            'site-index' => true,
            'course' => false,
            'mod' => false,
            'mod-quiz-attempt' => true,
            'mod-lesson-view' => true,
        );
    }

    /**
     * @return bool
     */
    function instance_allow_multiple() {
        return false;
    }
}
