<?php
/**
 * Library of functions and constants for module flashcard
 * @package mod-flashcard
 * @category mod
 * @author Gustav Delius
 * @contributors Valery Fremaux
 * @version Moodle 2.0
 */
/**
 * Includes and requires
 */
if (file_exists($CFG->libdir . '/filesystemlib.php')) {
    require_once($CFG->libdir . '/filesystemlib.php');
} else {
    require_once($CFG->dirroot . '/mod/flashcard/filesystemlib.php');
}
require_once($CFG->dirroot . '/lib/ddllib.php');
require_once($CFG->dirroot . '/mod/flashcard/locallib.php');

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will create a new instance and return the id number 
 * of the new instance.
 * @uses $COURSE, $DB
 */
function flashcard_add_instance($flashcard) {
    global $COURSE, $DB;

    $flashcard->timemodified = time();

    if (!isset($flashcard->starttimeenable)) {
        $flashcard->starttime = 0;
    }

    if (!isset($flashcard->endtimeenable)) {
        $flashcard->endtime = 0;
    }

    $newid = $DB->insert_record('flashcard', $flashcard);

    return $newid;
}

/**
 * Given an object containing all the necessary data, 
 * (defined by the form in mod.html) this function 
 * will update an existing instance with new data.
 * @uses $COURSE, $DB
 *
 */
function flashcard_update_instance($flashcard) {
    global $COURSE, $DB;

    $flashcard->timemodified = time();
    $flashcard->id = $flashcard->instance;

    // Make physical repository for customisation
    if (!file_exists($COURSE->id . '/moddata/flashcard/' . $flashcard->id)) {
        filesystem_create_dir($COURSE->id . '/moddata/flashcard/' . $flashcard->id, FS_RECURSIVE);
    }

    if (!isset($flashcard->starttimeenable)) {
        $flashcard->starttime = 0;
    }

    if (!isset($flashcard->endtimeenable)) {
        $flashcard->endtime = 0;
    }

    return $DB->update_record('flashcard', $flashcard);
}

/**
 * Given an ID of an instance of this module, 
 * this function will permanently delete the instance 
 * and any data that depends on it.  
 * @uses $COURSE, $DB
 */
function flashcard_delete_instance($id) {
    global $COURSE, $DB;

    // clear anyway what remains here
    filesystem_clear_dir($COURSE->id . '/moddata/flashcard/' . $id, FS_FULL_DELETE);

    if (!$flashcard = $DB->get_record('flashcard', array('id' => $id))) {
        return false;
    }

    $result = true;

    // Delete any dependent records here

    $DB->delete_records('flashcard_card', array('flashcardid' => $flashcard->id));

    if (!$DB->delete_records('flashcard', array('id' => $flashcard->id))) {
        $result = false;
    }

    return $result;
}

/**
 * Return a small object with summary information about what a 
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 */
function flashcard_user_outline($course, $user, $mod, $flashcard) {
    return $return;
}

/**
 * Print a detailed representation of what a  user has done with 
 * a given particular instance of this module, for user activity reports.
 */
function flashcard_user_complete($course, $user, $mod, $flashcard) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity 
 * that has occurred in flashcard activities and print it out. 
 * Return true if there was output, or false is there was none.
 * @uses $CFG
 */
function flashcard_print_recent_activity($course, $isteacher, $timestart) {
    global $CFG;

    return false;  //  True if anything was printed, otherwise false 
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such 
 * as sending out mail, toggling flags etc ... 
 * @uses $CFG
 *
 */
function flashcard_cron() {
    global $CFG, $DB;

    // get all flashcards
    $flashcards = $DB->get_records('flashcard');

    foreach ($flashcards as $flashcard) {
        if (!$flashcard->autodowngrade) continue;
        if ($flashcard->starttime != 0 && time() < $flashcard->starttime) continue;
        if ($flashcard->endtime != 0 && time() > $flashcard->endtime) continue;

        $cards = $DB->get_records_select('flashcard_card', 'flashcardid = ? AND deck > 1', array($flashcard->id));
        foreach ($cards as $card) {
            // downgrades to deck 3 (middle low)
            if ($flashcard->decks > 3) {
                if ($card->deck == 4 && time() > $card->lastaccessed + ($flashcard->deck4_delay * HOURSECS + $flashcard->deck4_release * HOURSECS)) {
                    $DB->set_field('flashcard_card', 'deck', 3, array('id' => $card->id));
                }
            }
            // downgrades to deck 2 (middle)
            if ($flashcard->decks > 2) {
                if ($card->deck == 3 && time() > $card->lastaccessed + ($flashcard->deck3_delay * HOURSECS + $flashcard->deck3_release * HOURSECS)) {
                    $DB->set_field('flashcard_card', 'deck', 2, array('id' => $card->id));
                }
            }
            // downgrades to deck 1 (difficult)
            if ($card->deck == 2 && time() > $card->lastaccessed + ($flashcard->deck2_delay * HOURSECS + $flashcard->deck2_release * HOURSECS)) {
                $DB->set_field('flashcard_card', 'deck', 1, array('id' => $card->id));
            }
        }
    }

    return true;
}

/**
 * Must return an array of grades for a given instance of this module, 
 * indexed by user.  It also returns a maximum allowed grade.
 *
 *    $return->grades = array of grades;
 *    $return->maxgrade = maximum allowed grade;
 *
 *    return $return;
 */
function flashcard_grades($flashcardid) {
    return NULL;
}

/**
 * Must return an array of user records (all data) who are participants
 * for a given instance of flashcard. Must include every user involved
 * in the instance, independient of his role (student, teacher, admin...)
 * See other modules as example.
 * @uses $DB
 */
function flashcard_get_participants($flashcardid) {
    global $DB;

    $userids = $DB->get_records_menu('flashcard_card', array('flashcardid' => $flashcardid), '', 'userid,id');
    if ($userids) {
        $users = $DB->get_records_list('user', 'id', array_keys($userids));
    }

    if (!empty($users)) return $users;

    return false;
}

/**
 * This function returns if a scale is being used by one flashcard
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 */
function flashcard_scale_used($flashcardid, $scaleid) {

    $return = false;

    //$rec = get_record("flashcard","id","$flashcardid","scale","-$scaleid");
    //
    //if (!empty($rec)  && !empty($scaleid)) {
    //    $return = true;
    //}

    return $return;
}

function flashcard_supports($feature) {
    switch ($feature) {
        case FEATURE_MOD_INTRO: return true;
        case FEATURE_GRADE_HAS_GRADE: return true;
        case FEATURE_GRADE_OUTCOMES: return false;
        case FEATURE_RATE: return false;
        case FEATURE_GROUPS: return false;
        case FEATURE_GROUPINGS: return false;
        case FEATURE_GROUPMEMBERSONLY: return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return false;
        case FEATURE_BACKUP_MOODLE2: return true;

        default: return null;
    }
}


function flashcard_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
  global $CFG, $DB, $USER;

  require_login($course, false, $cm);

  $itemid = (int) array_shift($args);

  require_course_login($course, true, $cm);

  $relativepath = implode('/', $args);
  $fullpath = "/$context->id/mod_flashcard/$filearea/$itemid/$relativepath";

  $fs = get_file_storage();
  if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
    return false;
  }
/*
  if (!$cm = get_coursemodule_from_id('journal', $cm->id)) {
    return false;
  }

  if (!$journal = $DB->get_record("journal", array("id" => $cm->instance))) {
    return false;
  }

 // $userjournal = journal_get_userjournal($journal, $USER);
  if(!$entry = $DB->get_record('journal_entries', array('id'=>$itemid))) {
    return false;
  }

 // $context = get_context_instance(CONTEXT_MODULE, $context->id);

  //allow if it is my own entry or teacher 
  if (!has_capability('mod/journal:manageentries', $context)) {
    require_capability('mod/journal:addentries', $context);
    //plus my own journal entry
    if ($entry->userid != $USER->id) {
      return false;
    }
  }
*/
  // finally send the file
  send_stored_file($file, 0, 0, true); // download MUST be forced - security!

  return false;
}


/**
 * Scale is not used anywhere by flashcard module
 *
 * @param int $scaleid
 * @return bool
 */
function flashcard_scale_used_anywhere($scaleid) {
    return false;
}
