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

/**
 * @module blocks_attendance_tracker/tracker
 */
define(['jquery'], function($) {
    return {
        active_time : 0,
        is_window_active : true,
        popup_window_active : false,
        cfg : {},
        initialise: function ($user_id, $course_id, $is_popup, $url, $lesson_id, $quiz_id, $key) {
            this.cfg.user_id = $user_id;
            this.cfg.course_id = $course_id;
            this.cfg.is_popup = $is_popup;
            this.cfg.url = $url;
            this.cfg.lesson_id = $lesson_id;
            this.cfg.quiz_id = $quiz_id;
            this.cfg.key = $key;

            var $this = this;
            $this.addListeners();
            setInterval(function(){$this.startTracking();}, 1000); //execute every 1 second
        },

        addListeners : function(){
            var $this = this;

            $(window).focus(function() {
                $this.setWindowActive();
            });

            $(window).blur(function() {
                $this.setWindowInactive();
            });

            if(!$this.cfg.is_popup) {
                $(window).on('beforeunload', function(){
                    $this.addTime();
                });
            }
        },

        startTracking : function(){
            if(this.is_window_active){
                this.active_time++;
                //this.popup_window_active = false;
            }

            //javascript chat
            //if( (parent.parent.opener != null && parent.parent.opener.closed == false) && isPopup){
            //    try{
            //        if(window_flag){
            //            parent.parent.opener.popup_window_active = true;
            //        }else{
            //            parent.parent.opener.popup_window_active = false;
            //        }
            //    }catch(exception){parent.parent.close();}
            //
            //}else{
            //    if($this.cfg.is_popup && (parent.parent.opener == null || parent.parent.opener.closed) ){parent.parent.close();}
            //}

            //console.log('Active time: ' + this.active_time);

            if(this.active_time == 10){
                this.addTime();
            }
        },

        addTime : function(){
            var $this = this;
            var timeToAdd = this.active_time;
            this.active_time = 0;

            $.ajax({
                url: $this.cfg.url,
                data: {
                    time : timeToAdd,
                    user_id : $this.cfg.user_id,
                    course_id : $this.cfg.course_id,
                    lesson_id : $this.cfg.lesson_id,
                    quiz_id : $this.cfg.quiz_id,
                    key : $this.cfg.key
                }
            });
        },

        setWindowActive : function(){
            this.is_window_active = true;
        },

        setWindowInactive : function(){
            this.is_window_active = false;
        }
    };
});
