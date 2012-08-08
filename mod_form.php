<?php

/** 
* @package mod-flashcard
* @category mod
* @author Gustav Delius
* @author Valery Fremaux
* @author Tomasz Muras
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
*/

/**
* Requires and includes 
*/
require_once ($CFG->dirroot.'/course/moodleform_mod.php');
require_once ($CFG->libdir.'/questionlib.php');
require_once ($CFG->dirroot.'/mod/flashcard/locallib.php');

/**
* overrides moodleform for flashcard setup
*/
class mod_flashcard_mod_form extends moodleform_mod {

	function definition() {
		global $CFG, $COURSE, $DB;

        $mform    =& $this->_form;


        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'64'));
      	$mform->setType('name', PARAM_TEXT);
      	$mform->addRule('name', null, 'required', null, 'client');

      /// Introduction.
        $this->add_intro_editor(false, get_string('summary', 'flashcard'));

        //$mform->addHelpButton($elementname, $identifier, $component, $linktext, $suppresscheck)
        //$mform->setHelpButton($elementname, $buttonargs, $suppresscheck, $function)

        //$mform->addHelpButton('summary', 'import', 'flashcard');
        //$mform->setHelpButton('summary', array('writing', 'questions', 'richtext2'), false, 'editorhelpbutton');


        $startdatearray[] = &$mform->createElement('date_time_selector', 'starttime', '');
        $startdatearray[] = &$mform->createElement('checkbox', 'starttimeenable', '');
        $mform->addGroup($startdatearray, 'startfrom', get_string('starttime', 'flashcard'), ' ', false);
        $mform->disabledIf('startfrom', 'starttimeenable');

        $enddatearray[] = &$mform->createElement('date_time_selector', 'endtime', '');
        $enddatearray[] = &$mform->createElement('checkbox', 'endtimeenable', '');
        $mform->addGroup($enddatearray, 'endfrom', get_string('endtime', 'flashcard'), ' ', false);
        $mform->disabledIf('endfrom', 'endtimeenable');

        $mform->addElement('selectyesno', 'flipdeck', get_string('flipdeck', 'flashcard'));
        $mform->addHelpButton('flipdeck', 'flipdeck', 'flashcard');

        $options['2'] = 2;
        $options['3'] = 3;
        $options['4'] = 4;
        $mform->addElement('select', 'decks', get_string('decks', 'flashcard'), $options);
        $mform->setType('decks', PARAM_INT); 
        $mform->setDefault('decks', 2);
        $mform->addHelpButton('decks', 'decks', 'flashcard');

        $mform->addElement('selectyesno', 'autodowngrade', get_string('autodowngrade', 'flashcard'));
        $mform->addHelpButton('autodowngrade', 'autodowngrade', 'flashcard');

        $mform->addElement('text', 'deck2_release', get_string('deck2_release', 'flashcard'), array('size'=>'5'));
        $mform->addHelpButton('deck2_release', 'deck_release', 'flashcard');
        $mform->setType('deck2_release', PARAM_INT);
        $mform->setDefault('deck2_release', 96);
        $mform->addRule('deck2_release', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');
 
        $mform->addElement('text', 'deck3_release', get_string('deck3_release', 'flashcard'), array('size'=>'5'));
        $mform->setType('deck3_release', PARAM_INT);
        $mform->setDefault('deck3_release', 96);
        $mform->addRule('deck3_release', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck3_release', 'decks', 'eq', 2);

        $mform->addElement('text', 'deck4_release', get_string('deck4_release', 'flashcard'), array('size'=>'5'));
        $mform->setType('deck4_release', PARAM_INT);
        $mform->setDefault('deck4_release', 96);
        $mform->addRule('deck4_release', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck4_release', 'decks', 'neq', 4);

        $mform->addElement('text', 'deck1_delay', get_string('deck1_delay', 'flashcard'), array('size'=>'5'));
        $mform->addHelpButton('deck1_delay', 'deck_delay', 'flashcard');
        $mform->setType('deck1_delay', PARAM_INT);
        $mform->setDefault('deck1_delay', 48);
        $mform->addRule('deck1_delay', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');

        $mform->addElement('text', 'deck2_delay', get_string('deck2_delay', 'flashcard'), array('size'=>'5'));
        $mform->setType('deck2_delay', PARAM_INT);
        $mform->setDefault('deck2_delay', 96);
        $mform->addRule('deck2_delay', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');

        $mform->addElement('text', 'deck3_delay', get_string('deck3_delay', 'flashcard'), array('size'=>'5'));
        $mform->setType('deck3_delay', PARAM_INT);
        $mform->setDefault('deck3_delay', 168);
        $mform->addRule('deck3_delay', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck3_delay', 'decks', 'eq', 2);

        $mform->addElement('text', 'deck4_delay', get_string('deck4_delay', 'flashcard'), array('size'=>'5'));
        $mform->setType('deck4_delay', PARAM_INT);
        $mform->setDefault('deck4_delay', 336);
        $mform->addRule('deck4_delay', get_string('numericrequired', 'flashcard'), 'numeric', null, 'client');
        $mform->disabledIf('deck4_delay', 'decks', 'neq', 4);

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
	}

    /**	
	function definition_after_data(){
		$mform    =& $this->_form;
        $startfrom =&$mform->getElement('startfrom');
        $elements = $startfrom->getElements();
        print_object($elements[1]->getValue());
        if ($mform->getElementValue('starttime') != 0){
            $starttimeenable->setValue(true);
        }
	}*/
        public function validation($data, $files) {
	    $errors = parent::validation($data, $files);

            if ($data['starttime'] > $data['endtime']){
                $errors['endfrom'] = get_string('mustbehigherthanstart', 'flashcard');
            }
	    
	    if ($data['decks'] >= 2){
	        if ($data['deck1_delay'] > $data['deck2_delay']) {
	            $errors['deck2_delay'] = get_string('mustbegreaterthanabove');
	        }
	    }
	    if ($data['decks'] >= 3){
	        if ($data['deck2_delay'] > $data['deck3_delay']) {
	            $errors['deck3_delay'] = get_string('mustbegreaterthanabove');
	        }
	    }
	    if ($data['decks'] >= 4){
	        if ($data['deck3_delay'] > $data['deck4_delay']) {
	            $errors['deck4_delay'] = get_string('mustbegreaterthanabove');
	        }
	    }
	    return $errors;
	}

}
