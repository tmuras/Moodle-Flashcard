<?php 
/**
 * @package mod-flashcard
 * @category mod
 * @author Tomasz Muras <nexor1984@gmail.com>
 */
class restore_flashcard_activity_structure_step extends restore_activity_structure_step {
    
    protected function define_structure() {
        
        $paths = array();
        $paths[] = new restore_path_element('flashcard', '/activity/flashcard');
        $paths[] = new restore_path_element('flashcard_deck', '/activity/flashcard/group_decks/deck');
        
        if ($this->get_setting_value('userinfo')) {
            $paths[] = new restore_path_element('flashcard_card', '/activity/flashcard/group_cards/card');
        }
        
        return $this->prepare_activity_structure($paths);
    }
    
    protected function process_flashcard($data) {
        
        global $DB;
        
        $data = (object)$data;
        
        $oldid = $data->id;
        unset($data->id);
        
        $data->course = $this->get_courseid();
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->starttime = $this->apply_date_offset($data->starttime);
        $data->endtime = $this->apply_date_offset($data->endtime);
        
        $newid = $DB->insert_record('flashcard', $data);
        $this->apply_activity_instance($newid);
    }
    
    protected function process_flashcard_deck($data) {
        
        global $DB;
        
        $data = (object)$data;
        
        $oldid = $data->id;
        unset($data->id);
        
        $data->flashcardid = $this->get_new_parentid('flashcard');
        
        $newid = $DB->insert_record('flashcard_deckdata', $data);
        $this->set_mapping('flashcard_deck', $oldid, $newid);
     
    }
 
    protected function process_flashcard_card($data) {
        
        global $DB;
        
        $data = (object)$data;
        
        $oldid = $data->id;
        unset($data->id);

        $data->flashcardid = $this->get_new_parentid('flashcard');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->entryid = $this->get_mappingid('flashcard_deck', $data->entryid);
        
        $newid = $DB->insert_record('flashcard_card', $data);
    }
    
    protected function after_execute() {
        $this->add_related_files('mod_flashcard', 'intro', null);
        //$this->add_related_files('mod_flashcard_entries', 'text', null);
        //$this->add_related_files('mod_flashcard_entries', 'entrycomment', null);
    }
}
