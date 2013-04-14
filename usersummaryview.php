<?php

    /** 
    * This view provides a summary for the teacher
    * 
    * @package mod-flashcard
    * @category mod
    * @author Valery Fremaux, Gustav Delius
    * @contributors
    * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
    * @version Moodle 2.0
    */

    // security
    if (!defined('MOODLE_INTERNAL')){
        print_error("Illegal direct access to this screen");
    }

    if ($action == 'reset'){
        $userid = required_param('userid', PARAM_INT);
        $DB->delete_records('flashcard_card', array('flashcardid' => $flashcard->id, 'userid' => $userid));
    }

    require_once($CFG->dirroot.'/enrol/locallib.php');
    
    $course_context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
    $course = $DB->get_record('course', array('id'=>$COURSE->id), '*', MUST_EXIST);
    $manager = new course_enrolment_manager($PAGE,$course);
    $courseusers = $manager->get_users('lastname','ASC',0,250);

    $struser = get_string('username');
    $strdeckstates = get_string('deckstates', 'flashcard');
    $strcounts = get_string('counters', 'flashcard');
    
    $table = new html_table();
    $table->head = array("<b>$struser</b>", "<b>$strdeckstates</b>", "<b>$strcounts</b>");
    $table->size = array('30%', '50%', '20%');
    $table->width = '90%';
    
    echo $out;
    
    if (!empty($courseusers)){
        foreach($courseusers as $auser){
            $status = flashcard_get_deck_status($flashcard, $auser->id);
            $userbox = $OUTPUT->user_picture($auser);
            $userbox .= fullname($auser);
            if ($status){
                $flashcard->cm = &$cm;
                $deckbox = flashcard_print_deck_status($flashcard, $auser->id, $status, true);
                $countbox = flashcard_print_deckcounts($flashcard, true, $auser->id);
            } else {
                $deckbox = get_string('notinitialized', 'flashcard');
                $countbox = '';
            }
            $table->data[] = array($userbox, $deckbox, $countbox);
        }    
        echo html_writer::table($table);
    } else {
        echo '<center>';
        $OUTPUT->box(get_string('nousers', 'flashcard'));
        echo '</center>';
    }

