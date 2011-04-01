<?php

/** 
* a controller for the play view
* 
* @package mod-flashcard
* @category mod
* @author Valery Fremaux
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
* @version Moodle 2.0
*
* @usecase initialize
* @usecase reset
* @usecase igotit
* @usecase ifailed
*/

// security
if (!defined('MOODLE_INTERNAL')){
    die('Direct access to this script is forbidden.'); /// It must be included from a Moodle page.
}

/*---------------------------------- initialize a deck ---------------------------------*/
if ($action == 'initialize'){
    if ($initials = $DB->get_records_select('flashcard_card', "flashcardid = ? AND userid = ? AND deck = ? ", array($flashcard->id, $USER->id, $deck))){   
        $_SESSION['flashcard_initials'] = implode("','", array_keys($initials));
    }
    unset($_SESSION['flashcard_consumed']);
}
/*---------------------------------- reset a deck ---------------------------------*/
if ($action == 'reset'){
    $initials = explode("','", $_SESSION['flashcard_initials']);
    list($usql, $params) = $DB->get_in_or_equal(array_keys($initials));
    $DB->set_field_select('flashcard_card', 'deck', $deck, "id $usql ", $params);
    unset($_SESSION['flashcard_consumed']);         
}
/*---------------------------------- a card was declared right --------------------*/
if ($action == 'igotit'){       
    $card->id = required_param('cardid', PARAM_INT);
    $card = $DB->get_record('flashcard_card', array('id' => $card->id));    
    if ($card->deck < $flashcard->decks){
        $card->deck = $deck + 1;
    } else {
        // if in last deck, consume it !!
        if (array_key_exists('flashcard_consumed', $_SESSION)){
            $_SESSION['flashcard_consumed'] .= ','.$card->id;
        } else {
            $_SESSION['flashcard_consumed'] = $card->id;
        }
    }
    $card->lastaccessed = time();
    $card->accesscount++ ;
    if (!$DB->update_record('flashcard_card', $card)){
        print_error('dbcouldnotupdate', 'flashcard', '', get_string('cardinfo', 'flashcard'));
    }
}
/*------------------------------ a card was declared wrong -----------------------*/
if ($action == 'ifailed'){
    $card->id = required_param('cardid', PARAM_INT);
    $card = $DB->get_record('flashcard_card', array('id' => $card->id));
    $card->lastaccessed = time();
    $card->accesscount++ ;
    if (!$DB->update_record('flashcard_card', $card)){
        print_error('dbcouldnotupdate', 'flashcard', '', get_string('cardinfo', 'flashcard'));
    }
    if (array_key_exists('flashcard_consumed', $_SESSION)){
        $_SESSION['flashcard_consumed'] .= ','.$card->id;
    } else {
        $_SESSION['flashcard_consumed'] = $card->id;
    }
}
?>