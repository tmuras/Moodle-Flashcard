<?php

require_once($CFG->libdir . '/formslib.php');

class flashcard_cardsedit_form extends moodleform {

    protected $numelements = 10;

    protected function definition() {
        global $COURSE, $CFG, $DB, $PAGE;

        $mform = $this->_form;
        //$cards = $this->_customdata;

        $mform->addElement('hidden','id');
        $mform->addElement('hidden','view');
        
        for ($i = 0; $i < $this->numelements; $i++) {
            $mform->addElement('editor', "question[$i]", "QUESTION");
            $mform->addElement('editor', "answer[$i]", "ANSWER");
            $mform->addElement('hidden', "cardid[$i]");
        }
        

        //-------------------------------------------------------------------------------
//        $mform->addElement('header', 'general', get_string('general', 'form'));
//        echo 'ok';
        /*
          $mform->addElement('editor', 'page', get_string('content', 'page'));//, null, array('context'=>$context,'changeformat'=>1,'trusttext'=>1));
          return;

         */
        /*
          foreach($cards as $card) {
          $mform->addElement('editor', "question[{$card->id}]", 'question');//, null, array('context'=>$context,'changeformat'=>1,'trusttext'=>1));

          }
         */
        $mform->addElement('submit', 'addmore', "Save and add new page");

        $this->add_action_buttons();
    }

}
