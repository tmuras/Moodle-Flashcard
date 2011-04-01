<?php

    /** 
    * controller for summary view
    * 
    * @package mod-flashcard
    * @category mod
    * @author Gustav Delius
    * @contributors Valery Fremaux
    * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
    * @version Moodle 2.0
    *
    * @usecase reset
    */

if ($action == 'reset'){
   $userid = required_param('userid', PARAM_INT);
   $DB->delete_records('flashcard_card', array('flashcardid' => $flashcard->id, 'userid' => $userid));
}
?>