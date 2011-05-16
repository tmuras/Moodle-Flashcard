<?php

    /** 
    * This view allows checking deck states
    * 
    * @package mod-flashcard
    * @category mod
    * @author Valery Fremaux (valery.fremaux@club-internet.fr)
    * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
    */

require_once($CFG->libdir.'/formslib.php');

class flashcard_import_form extends moodleform{

    var $flashcardid;
    
    function flashcard_import_form($flashcardid){
        $this->flashcardid = $flashcardid;
        parent::moodleform();
    }

    function definition(){
        
        $mform =& $this->_form;
        $mform->addElement('hidden', 'a', $this->flashcardid); 
        $mform->addElement('hidden', 'what', 'doimport'); 
        $mform->addElement('hidden', 'view', 'edit'); 
        
        $mform->addElement('header', 'cardimport', ''); 
        
        $cardsepoptions[0] = ':';
        $cardsepoptions[1] = ';';
        $cardsepoptions[2] = '[CR]';
        $cardsepoptions[3] = '[CR][LF]';
        $mform->addElement('select', 'cardsep', get_string('cardsep', 'flashcard'), $cardsepoptions);

        $fieldsepoptions[0] = ',';
        $fieldsepoptions[1] = ':';
        $fieldsepoptions[2] = '[TAB]';
        $fieldsepoptions[3] = '[SP]';
        $mform->addElement('select', 'fieldsep', get_string('fieldsep', 'flashcard'), $fieldsepoptions);

        $mform->addElement('textarea', 'import', get_string('imported', 'flashcard'), array('ROWS' => 10, 'COLS' => 40));

        $mform->addElement('checkbox', 'confirm', get_string('confirm', 'flashcard'), get_string('importadvice', 'flashcard'));

        $this->add_action_buttons(true, get_string('import', 'flashcard'));
    }

}

?>