<?PHP //$Id: restorelib.php,v 1.4 2008/12/28 15:44:56 diml Exp $

    /**
    * This php script contains all the stuff to backup/restore
    * flashcard mods
    *
    * @package mod-flashcard
    * @category mod
    * @author Valery Fremaux (admin@ethnoinformatique.fr)
    * @version Moodle 2.0
    * 
    */

    //This is the "graphical" structure of the flashcard mod:
    //
    //           flashcard                                  
    //          (CL,pk->id)               
    //               |                       
    //               +-------------------------------------------+
    //               |                                           |
    //         flashcard_card                            flashcard_deckdata 
    //  (UL,pk->id, fk->flashcardid, fk->entryid)  (IL, pk->id, fk->flashcardid,files)
    //
    // Meaning: pk->primary key field of the table
    //          fk->foreign key to link with parent
    //          nt->nested field (recursive data)
    //          IL->instance level info
    //          CL->course level info
    //          UL->user level info
    //          files->table may have files
    //
    //-----------------------------------------------------------

    /**
    * restores a complete module
    * @param object $mod
    * @param object $restore
    * @uses $CFG
    */
    function flashcard_restore_mods($mod, $restore) {
        global $CFG;

        $status = true;

        //Get record from backup_ids
        $data = backup_getid($restore->backup_unique_code, $mod->modtype, $mod->id);

        if ($data) {
            //Now get completed xmlized object
            $info = $data->info;
            //traverse_xmlize($info);                                                                     //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //Now, build the FLASHCARD record structure
            $flashcard->course = $restore->course_id;
            $flashcard->name = backup_todb($info['MOD']['#']['NAME']['0']['#']);
            $flashcard->summary = backup_todb($info['MOD']['#']['SUMMARY']['0']['#']);
            $flashcard->summaryformat = backup_todb($info['MOD']['#']['SUMMARYFORMAT']['0']['#']);
            $flashcard->timemodified = backup_todb($info['MOD']['#']['TIMEMODIFIED']['0']['#']);
            $flashcard->starttime = backup_todb($info['MOD']['#']['STARTTIME']['0']['#']);
            $flashcard->endtime = backup_todb($info['MOD']['#']['ENDTIME']['0']['#']);
 			$flashcard->questionid = backup_todb($info['MOD']['#']['QUESTIONID']['0']['#']);
 			$flashcard->autodowngrade = backup_todb($info['MOD']['#']['AUTODOWNGRADE']['0']['#']);
 			$flashcard->decks = backup_todb($info['MOD']['#']['DECKS']['0']['#']);
 			$flashcard->deck2_release = backup_todb($info['MOD']['#']['DECK2_RELEASE']['0']['#']);
 			$flashcard->deck3_release = backup_todb($info['MOD']['#']['DECK3_RELEASE']['0']['#']);
 			$flashcard->deck4_release = backup_todb($info['MOD']['#']['DECK4_RELEASE']['0']['#']);
 			$flashcard->deck1_delay = backup_todb($info['MOD']['#']['DECK1_DELAY']['0']['#']);
 			$flashcard->deck2_delay = backup_todb($info['MOD']['#']['DECK2_DELAY']['0']['#']);
 			$flashcard->deck3_delay = backup_todb($info['MOD']['#']['DECK3_DELAY']['0']['#']);
 			$flashcard->deck4_delay = backup_todb($info['MOD']['#']['DECK4_DELAY']['0']['#']);
 			$flashcard->questionsmediatype = backup_todb($info['MOD']['#']['QUESTIONSMEDIATYPE']['0']['#']);
 			$flashcard->answersmediatype = backup_todb($info['MOD']['#']['ANSWERSMEDIATYPE']['0']['#']);
 			$flashcard->flipdeck = backup_todb($info['MOD']['#']['FLIPDECK']['0']['#']);
                           
            //The structure is equal to the db, so insert the flashcard
            $newid = $DB->insert_record ('flashcard', $flashcard);

            //Check the question if exists and is a match or set it to 0
            if (! $question = $DB->get_record('question', array('id' => $flashcard->questionid)) || $question->qtype != 'match'){
                $flashcard->questionid = 0;
            }

            //Do some output     
            echo '<ul><li>' . get_string('modulename', 'flashcard') . " \"" . $flashcard->name . "\"<br>";
            backup_flush(300);

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, $mod->modtype, $mod->id, $newid);

                $status = $status && flashcard_deckdata_restore_mods ($mod->id, $newid, $info, $restore, $flashcard);

                //Now check if want to restore user data and do it.
                if ($restore->mods['flashcard']->userinfo) {
                    //Restore user decks
                    $status = $status && flashcard_cards_restore_mods($mod->id, $newid, $info, $restore);
                }
                
                //Now restore files
                $status = $status && flashcard_restore_files($mod->id, $newid, $restore);
            } 
            else {
                $status = false;
            }

            //Finalize ul        
            echo '</ul>';

        } else {
            $status = false;
        }

        return $status;
    }

    /**
    * This function restores the deck
    * @param int $old_flashcard_id 
    * @param int $new_flashcard_id
    * @param array $info
    * @param $restore
    * @uses $CFG 
    */
    function flashcard_deckdata_restore_mods($old_flashcard_id, $new_flashcard_id, $info, $restore, &$flashcard) {
        global $CFG;

        $status = true;

        //Get the deck array
        $cards = $info['MOD']['#']['DECK']['0']['#']['CARD'];

        //Iterate over cards
        for($i = 0; $i < sizeof($cards); $i++) {
            $card_info  = $cards[$i];
            //traverse_xmlize($card_info);                                                               //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($card_info['#']['ID']['0']['#']);

            //Now, build the DECKDATA record structure
            $card->flashcardid = $new_flashcard_id;
            $card->questiontext = backup_todb($card_info['#']['QUESTIONTEXT']['0']['#']);
            $card->answertext = backup_todb($card_info['#']['ANSWERTEXT']['0']['#']);

            // we must recode file locations if we are using media
            if ($flashcard->questionsmediatype){
                $card->questiontext = preg_replace("/moddata\\/flashcard\\/{$old_flashcard_id}/", "moddata/flashcard/{$new_flashcard_id}", $card->questiontext);
            }

            if ($flashcard->answersmediatype){
                $card->answertext = preg_replace("/moddata\\/flashcard\\/{$old_flashcard_id}/", "moddata/flashcard/{$new_flashcard_id}", $card->answertext);
            }


            //The structure is equal to the db, so insert the card
            $newid = $DB->insert_record ('flashcard_deckdata', $card);

            //Do some output
            if (($i+1) % 50 == 0) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'flashcard_deckdata', $oldid, $newid);
            } else {
                $status = false;
            }
        }

        return $status;
    }

    /**
    * This function restores cards in users' decks
    * @param int $old_flashcard_id 
    * @param int $new_flashcard_id
    * @param array $info
    * @param $restore
    * @uses $CFG 
    */
    function flashcard_cards_restore_mods($old_flashcard_id, $new_flashcard_id, $info, $restore) {
        global $CFG;

        $status = true;

        //Get the deck array
        $cards = $info['MOD']['#']['CARDS']['0']['#']['CARD'];

        //Iterate over cards
        for($i = 0; $i < sizeof($cards); $i++) {
            $card_info  = $cards[$i];
            //traverse_xmlize($card_info);                                                               //Debug
            //print_object ($GLOBALS['traverse_array']);                                                  //Debug
            //$GLOBALS['traverse_array']="";                                                              //Debug

            //We'll need this later!!
            $oldid = backup_todb($card_info['#']['ID']['0']['#']);

            //Now, build the DECKDATA record structure
            $card->flashcardid = $new_flashcard_id;
            $card->userid = backup_todb($card_info['#']['USERID']['0']['#']);
            $card->entryid = backup_todb($card_info['#']['ENTRYID']['0']['#']);
            $card->deck = backup_todb($card_info['#']['DECK']['0']['#']);
            $card->lastaccessed = backup_todb($card_info['#']['LASTACCESSED']['0']['#']);
            $card->accesscount = backup_todb($card_info['#']['ACCESSCOUNT']['0']['#']);

            //We have to recode the userid field
            $user = backup_getid($restore->backup_unique_code, 'user', $card->userid);
            if ($user) {
                $card->userid = $user->new_id;
            }

            //We have to recode the entryid field
            $deckcard = backup_getid($restore->backup_unique_code, 'flashcard_deckdata', $card->entryid);
            if ($deckcard) {
                $card->entryid = $deckcard->new_id;
            }

            //The structure is equal to the db, so insert the card
            $newid = $DB->insert_record ('flashcard_card', $card);

            //Do some output
            if (($i+1) % 50 == 0) {
                echo ".";
                if (($i+1) % 1000 == 0) {
                    echo "<br />";
                }
                backup_flush(300);
            }

            if ($newid) {
                //We have the newid, update backup_ids
                backup_putid($restore->backup_unique_code, 'flashcard_card', $oldid, $newid);
            } else {
                $status = false;
            }
        }

        return $status;
    }

    //This function copies the forum related info from backup temp dir to course moddata folder,
    //creating it if needed and recoding everything (forum id and post id)
    function flashcard_restore_files ($oldflashid, $newflashid, $restore) {
        global $CFG;

        $status = true;
        $todo = false;
        $moddata_path = "";
        $flashcard_path = "";
        $temp_path = "";

        //First, we check to "course_id" exists and create is as necessary
        //in CFG->dataroot
        $dest_dir = $CFG->dataroot."/".$restore->course_id;
        $status = check_dir_exists($dest_dir, true);

        //First, locate course's moddata directory
        $moddata_path = $CFG->dataroot."/".$restore->course_id."/".$CFG->moddata;

        //Check it exists and create it
        $status = check_dir_exists($moddata_path, true);

        //Now, locate forum directory
        if ($status) {
            $flashcard_path = $moddata_path."/flashcard";
            //Check if exists and create it
            $status = check_dir_exists($flashcard_path, true);
        }

        //Now locate the temp dir we are restoring from
        if ($status) {
            $temp_path = $CFG->dataroot."/temp/backup/".$restore->backup_unique_code.
                         "/moddata/flashcard/".$oldflashid;
            //Check it exists
            if (is_dir($temp_path)) {
                $todo = true;
            }
        }

        //If todo, we create the neccesary dirs in course moddata/forum
        if ($status and $todo) {
            //First this flashcard id
            $this_flashcard_path = $flashcard_path."/".$newflashid;
            $status = check_dir_exists($this_flashcard_path, true);
            //And now, copy temp_path to flashcard_path
            $status = backup_copy_file($temp_path, $this_flashcard_path);
        }

        return $status;
    }

?>