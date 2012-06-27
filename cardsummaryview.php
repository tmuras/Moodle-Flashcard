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
        die('Direct access to this script is forbidden.'); /// It must be included from a Moodle page.
    }

    /**
    if ($action != ''){
        include "{$CFG->dirroot}/mod/flashcard/cardsummaryview.controller.php";
    }
    */

    $cards = flashcard_get_card_status($flashcard);
        
    $strcard = get_string('card', 'flashcard');
    $strviewed = get_string('viewed', 'flashcard');
    $strdecks = get_string('decks', 'flashcard');

    $table->head = array("<b>$strcard</b>", "<b>$strdecks</b>", "<b>$strviewed</b>");
    $table->size = array('30%', '35%', '35%');
    $table->width = "90%";
    
    foreach($cards as $cardquestion => $acard){
        $cardcounters = flashcard_print_cardcounts($flashcard, $acard, true);
        $table->data[] = array(format_string($cardquestion), $cardcounters, $acard->accesscount);
    }    
    echo $out;
    print_table($table);
?>