<?php
/** 
* a controller for the play view
* 
* @package mod-flashcard
* @category mod
* @author Valery Fremaux
* @license http://www.gnu.org/copyleft/gpl.html GNU Public License
*
* @usecase add
* @usecase delete
* @usecase save
* @usecase import
* @usecase doimport
*/

/******************************** Add new blank fields *****************************/
if ($action == 'add'){
    $add = required_param('add', PARAM_INT);
    $card->flashcardid = $flashcard->id;
    $users = get_records_menu('flashcard_card', 'flashcardid', $flashcard->id, '', 'DISTINCT userid, id');
    for($i = 0 ; $i < $add ; $i++){
        if (!$newcardid = insert_record('flashcard_deckdata', $card)){
            error ("Could not add card to deck");
        }
        if ($users){
            foreach(array_keys($users) as $userid){
                $deckcard->flashcardid = $flashcard->id;
                $deckcard->entryid = $newcardid;
                $deckcard->userid = $userid;
                $deckcard->lastaccessed = 0;
                $deckcard->deck = 1;
                $deckcard->accesscount = 0;
                if (!insert_record('flashcard_card', $deckcard)){
                    error ("Could not bind card to user $userid deck");
                }
            }
        }
    }
}
/******************************** Delete a set of records *****************************/
if ($action == 'delete'){
    $items = required_param('items', PARAM_INT);
    if (is_array($items)) $items = implode(',', $items);
    $items = str_replace(",", "','", $items);

    if (!delete_records_select('flashcard_deckdata', " id IN ('$items') ")){
        error ("Could not add card to deck");
    }

    if (!delete_records_select('flashcard_card', " entryid IN ('$items') ")){
        error ("Could not add card to deck");
    }
}
/******************************** Save and update all questions *****************************/
if ($action == 'save'){
	$keys = array_keys($_POST);				// get the key value of all the fields submitted
	$qkeys = preg_grep('/^q/' , $keys);  	// filter out only the status
	$akeys = preg_grep('/^a/' , $keys);  	// filter out only the assigned updating

    foreach($qkeys as $akey){
        preg_match("/[qi](\d+)/", $akey, $matches);
        $card->id = $matches[1];
        $card->flashcardid = $flashcard->id;
        if ($flashcard->questionsmediatype != FLASHCARD_MEDIA_IMAGE_AND_SOUND){
            $card->questiontext = required_param("q{$card->id}", PARAM_TEXT);
        } else {
            // combine image and sound in one single field
            $card->questiontext = required_param("i{$card->id}", PARAM_TEXT).'@'.required_param("s{$card->id}", PARAM_TEXT);
        }
        if ($flashcard->answersmediatype != FLASHCARD_MEDIA_IMAGE_AND_SOUND){
            $card->answertext = required_param("a{$card->id}", PARAM_TEXT);
        } else {
            // combine image and sound in one single field
            $card->answertext = required_param("i{$card->id}", PARAM_TEXT).'@'.required_param("s{$card->id}", PARAM_TEXT);
        }
        if (!update_record('flashcard_deckdata', $card)){
            error("Could not update deck card");
        }
    }
}
/******************************** Prepare import *****************************/
if ($action == 'import'){
    include 'import_form.php';
    $mform = new flashcard_import_form($flashcard->id);
    print_heading(get_string('importingcards', 'flashcard').helpbutton('import', get_string('import', 'flashcard'), 'flashcard', true, false, '', true));
    $mform->display();
    return -1;
}
/******************************** Perform import *****************************/
if ($action == 'doimport'){
    include 'import_form.php';
    $form = new flashcard_import_form($flashcard->id);
    
    $CARDSEPPATTERNS[0] = ':';
    $CARDSEPPATTERNS[1] = ';';
    $CARDSEPPATTERNS[2] = "\n";
    $CARDSEPPATTERNS[3] = "\r\n";

    $FIELDSEPPATTERNS[0] = ',';
    $FIELDSEPPATTERNS[1] = ':';
    $FIELDSEPPATTERNS[2] = " ";
    $FIELDSEPPATTERNS[3] = "\t";

    if ($data = $form->get_data()){
    
        if (!empty($data->confirm)){
    
            $cardsep = $CARDSEPPATTERNS[$data->cardsep];
            $fieldsep = $FIELDSEPPATTERNS[$data->fieldsep];
            
            // filters comments and non significant lines
            $data->import = preg_replace("/^#.*\$/m", '', $data->import);
            $data->import = preg_replace("/^\\/.*\$/m", '', $data->import);
            $data->import = preg_replace('/^\\s+$/m', '', $data->import);
            $data->import = preg_replace("/(\\r?\\n)\\r?\\n/", '$1', $data->import);
            $data->import = trim($data->import);
            
            $pairs = explode($cardsep, $data->import);
            if (!empty($pairs)){
                /// first integrity check
                $report->cards = count($pairs);
                $report->badcards = 0;
                $report->goodcards = 0;
                $inputs = array();
                foreach($pairs as $pair){
                    if (strstr($pair, $fieldsep) === false){
                        $report->badcards++;
                    } else {
                        $input = new StdClass;
                        list($input->question, $input->answer) = explode($fieldsep, $pair);
                        if (empty($input->question) || empty($input->answer)){
                            $report->badcards++;
                        } else {
                            $inputs[] = $input;
                            $report->goodcards++;
                        }
                    }
                }
    
                if ($report->badcards == 0){
                    /// everything ok
                    /// reset all data
                    delete_records('flashcard_card', 'flashcardid', $flashcard->id);
                    delete_records('flashcard_deckdata', 'flashcardid', $flashcard->id);
        
                    // insert new cards
                    foreach($inputs as $input){
                        $deckcard->flashcardid = $flashcard->id;
                        $deckcard->questiontext = $input->question;
                        $deckcard->answertext = $input->answer;
                        insert_record('flashcard_deckdata', $deckcard);
                    }
                    
                    // reset questionid in flashcard instance
                    set_field('flashcard', 'questionid', 0, 'id', $flashcard->id);
                    
                }

                $reportstr = get_string('importreport', 'flashcard').'<br/>';
                $reportstr = get_string('cardsread', 'flashcard').$report->cards.'<br/>';
                if ($report->badcards){
                    $reportstr .= get_string('goodcards', 'flashcard').$report->goodcards.'<br/>';
                    $reportstr .= get_string('badcards', 'flashcard').$report->badcards.'<br/>';
                }
                
                echo "<center>";
                print_box($reportstr, 'reportbox');
                echo "</center>";
            }
        }
    }
}
?>