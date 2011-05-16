<?php

    /** 
    * This page prints a particular instance of a flashcard
    * 
    * @package mod-flashcard
    * @category mod
    * @author Gustav Delius
    * @author Valery Fremaux
    * @author Tomasz Muras
    * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
    */

    require_once("../../config.php");
    require_once($CFG->dirroot.'/mod/flashcard/lib.php');

    $id = required_param('id', PARAM_INT);   // course

    if (! $course = $DB->get_record('course', array('id' => $id))) {
        error("Course ID is incorrect");
    }

    require_login($course->id);

    add_to_log($course->id, 'flashcard', 'view all', "index.php?id=$course->id", '');


/// Get all required strings

    $strflashcards = get_string('modulenameplural', 'flashcard');
    $strflashcard  = get_string('modulename', 'flashcard');

/// Print the header

    $navlinks[] = array('name' => $strflashcards,
                        'url' => '',
                        'type' => 'title');
    $navigation = build_navigation($navlinks);

    print_header("$course->shortname: $strflashcards", "$course->fullname", $navigation, '', '', true, '', navmenu($course));

/// Get all the appropriate data

    if (! $flashcards = get_all_instances_in_course('flashcard', $course)) {
        notice(get_string('noflashcards', 'flashcard'), "../../course/view.php?id=$course->id");
        die;
    }

/// Print the list of instances (your module will probably extend this)

    $timenow = time();
    $strname  = get_string('name');
    $strweek  = get_string('week');
    $strtopic  = get_string('topic');

    if ($course->format == 'weeks') {
        $table->head  = array ($strweek, $strname);
        $table->align = array ('center', 'left');
    } else if ($course->format == 'topics') {
        $table->head  = array ($strtopic, $strname);
        $table->align = array ('center', 'left', 'left', 'left');
    } else {
        $table->head  = array ($strname);
        $table->align = array ('left', 'left', 'left');
    }

    foreach ($flashcards as $flashcard) {
        if (!$flashcard->visible) {
            //Show dimmed if the mod is hidden
            $link = "<a class=\"dimmed\" href=\"view.php?id={$flashcard->coursemodule}\">{$flashcard->name}</a>";
        } else {
            //Show normal if the mod is visible
            $link = "<a href=\"view.php?id={$flashcard->coursemodule}\">{$flashcard->name}</a>";
        }

        if ($course->format == 'weeks' or $course->format == 'topics') {
            $table->data[] = array ($flashcard->section, $link);
        } else {
            $table->data[] = array ($link);
        }
    }

    echo '<br/>';

    print_table($table);

/// Finish the page

    print_footer($course);

?>
