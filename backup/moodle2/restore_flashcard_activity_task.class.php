<?php 
/**
 * @package mod-flashcard
 * @category mod
 * @author Tomasz Muras <nexor1984@gmail.com>
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/flashcard/backup/moodle2/restore_flashcard_stepslib.php');

class restore_flashcard_activity_task extends restore_activity_task {
    
    protected function define_my_settings() {}
    
    protected function define_my_steps() {
        $this->add_step(new restore_flashcard_activity_structure_step('flashcard_structure', 'flashcard.xml'));
    }
    
    static public function define_decode_contents() {
        
        $contents = array();
        $contents[] = new restore_decode_content('flashcard', array('intro'), 'flashcard');
        //$contents[] = new restore_decode_content('flashcard_entries', array('text', 'entrycomment'), 'flashcard_entry');
        
        return $contents;
    }
    
    static public function define_decode_rules() {
        return array();
        $rules = array();
        $rules[] = new restore_decode_rule('flashcardINDEX', '/mod/flashcard/index.php?id=$1', 'course');
        $rules[] = new restore_decode_rule('flashcardVIEWBYID', '/mod/flashcard/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('flashcardREPORT', '/mod/flashcard/report.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('flashcardEDIT', '/mod/flashcard/edit.php?id=$1', 'course_module');

        return $rules;

    }
    static public function define_restore_log_rules() {
        $rules = array();
        
        return $rules;
    }
}
