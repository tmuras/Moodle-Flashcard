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
    * @version Moodle 2.0
    */
    
    /* @var $OUTPUT core_renderer */

    require_once('../../config.php');
    require_once($CFG->dirroot.'/mod/flashcard/lib.php');
    require_once($CFG->dirroot.'/mod/flashcard/locallib.php');

    $id = optional_param('id', '', PARAM_INT);    // Course Module ID, or
    $a = optional_param('a', '', PARAM_INT);     // flashcard ID
    $view = optional_param('view', 'checkdecks', PARAM_ACTION);     // view
    $subview = optional_param('subview', '', PARAM_ACTION);     // subview
    $action = optional_param('what', '', PARAM_ACTION);     // command
    
    $thisurl = $CFG->wwwroot.'/mod/flashcard/view.php';
    
    $url = new moodle_url('/mod/flashcard/view.php');
    $PAGE->set_url($url);
    if ($id) {
        if (! $cm = $DB->get_record('course_modules', array('id' => $id))) {
            print_error('invalidcoursemodule');
        }
        if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
            print_error('coursemisconf');
        }
        if (! $flashcard = $DB->get_record('flashcard', array('id' => $cm->instance))) {
            print_error('errorinvalidflashcardid', 'flashcard');
        }
    } else {
        if (! $flashcard = $DB->get_record('flashcard', array('id' => $a))) {
            print_error('invalidcoursemodule');
        }
        if (! $course = $DB->get_record('course', array('id' => $flashcard->course))) {
            print_error('coursemisconf');
        }
        if (! $cm = get_coursemodule_from_instance('flashcard', $flashcard->id, $course->id)) {
            print_error('errorinvalidflashcardid', 'flashcard');
        }
    }

    require_login($course->id);
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);

    add_to_log($course->id, 'flashcard', 'view', $thisurl."?id={$cm->id}", "{$flashcard->name}");

/// Print the page header

    $strflashcards = get_string('modulenameplural', 'flashcard');
    $strflashcard  = get_string('modulename', 'flashcard');
    $navlinks[] = array('name' => $strflashcards, 'link' => "index.php?id={$course->id}", 'type' => 'url');
    $navlinks[] = array('name' => $flashcard->name, 'link' => '', 'type' => 'title');
    $navigation = build_navigation($navlinks);
    $out = print_header( "$course->shortname: $flashcard->name", 
                  "$course->fullname",
                  $navigation, 
                  '', 
                  '', 
                  true, 
                  update_module_button($cm->id, $course->id, $strflashcard), 
                  navmenu($course, $cm), false, '', true);

    
/// non visible trap for timerange (security)
    if (!has_capability('moodle/course:viewhiddenactivities', $context) && !$cm->visible){
        notice(get_string('activityiscurrentlyhidden'));
    }

/// non manager trap for timerange

    if (!has_capability('mod/flashcard:manage', $context)){
        $now = time();
        if (($flashcard->starttime != 0 && $now < $flashcard->starttime) || ($flashcard->endtime != 0 && $now > $flashcard->endtime)){
            notice('outoftimerange', 'flashcard');
        }
    }    

/// loads "per instance" customisation styles

    $localstyle = "{$course->id}/moddata/flashcard/{$flashcard->id}/flashcard.css";
    if (file_exists("{$CFG->dataroot}/{$localstyle}")){
        if ($CFG->slasharguments) {
            $localstyleurl = $CFG->wwwroot.'/file.php/'.$localstyle;
        } else {
            if ($CFG->slasharguments){
                $localstyleurl = $CFG->wwwroot.'/file.php?file='.$localstyle;
            } else {
                $localstyleurl = $CFG->wwwroot.'/file.php'.$localstyle;
            }
        }
        echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"{$localstyleurl}\" />";
    }

/// Determine the current tab

    switch($view){
        case 'checkdecks' : $currenttab = 'play'; break;
        case 'play' : $currenttab = 'play'; break;
        case 'freeplay' : $currenttab = 'freeplay'; break;
        case 'summary' : $currenttab = 'summary'; break;
        case 'edit' : $currenttab = 'edit'; break;
        case 'add' : $currenttab = 'edit'; break;        
        default : $currenttab = 'play';
    }
    
    if($action == 'import') {
        $currenttab = 'import';
    }
    
/// print tabs
    if (!preg_match("/(summary)|(freeplay)|(play)|(checkdecks)|(edit)|(add)/", $view)) $view = 'checkdecks';
    $tabname = get_string('leitnergame', 'flashcard');
    $row[] = new tabobject('play', $thisurl."?id={$cm->id}&amp;view=checkdecks", $tabname);
    $tabname = get_string('freegame', 'flashcard');
    $row[] = new tabobject('freeplay', $thisurl."?view=freeplay&amp;id={$cm->id}", $tabname);
    if (has_capability('mod/flashcard:manage', $context)){
        $tabname = get_string('teachersummary', 'flashcard');
        $row[] = new tabobject('summary', $thisurl."?view=summary&amp;id={$cm->id}&amp;subview=byusers", $tabname);
        $tabname = get_string('edit', 'flashcard');
        $row[] = new tabobject('edit', $thisurl."?view=edit&amp;id={$cm->id}", $tabname);
        $tabname = get_string('import', 'flashcard');
        $row[] = new tabobject('import', $thisurl."?what=import&amp;view=edit&amp;id={$cm->id}", $tabname);  
    }
    $tabrows[] = $row;
    
    $activated = array();

/// print second line

    if ($view == 'summary'){
        switch($subview){
            case 'bycards' : {
                $currenttab = 'bycards';
                $activated[] = 'summary'; 
                break;
            }
            default : {
                $currenttab = 'byusers';
                $activated[] = 'summary';
            }
        }

        $tabname = get_string('byusers', 'flashcard');
        $row1[] = new tabobject('byusers', $thisurl."?id={$cm->id}&amp;view=summary&amp;subview=byusers", $tabname);
        $tabname = get_string('bycards', 'flashcard');
        $row1[] = new tabobject('bycards', $thisurl."?id={$cm->id}&amp;view=summary&amp;subview=bycards", $tabname);
        $tabrows[] = $row1;
    }

    $out .= print_tabs($tabrows, $currenttab, null, $activated, true);

/// print summary

    if (!empty($flashcard->summary)) {
        $out .= $OUTPUT->box_start();
        $out .= format_text($flashcard->summary, $flashcard->summaryformat, NULL, $course->id);
        $out .= $OUTPUT->box_end();
    }

/// print active view
    switch ($view){
        case 'summary' : 
            if (!has_capability('mod/flashcard:manage', $context)){
                redirect($thisurl."?view=checkdecks&amp;id={$cm->id}");
            }
            if ($subview == 'bycards'){
                include $CFG->dirroot.'/mod/flashcard/cardsummaryview.php';
            } else {
                include $CFG->dirroot.'/mod/flashcard/usersummaryview.php';
            }
            break;
        case 'edit' : 
            if (!has_capability('mod/flashcard:manage', $context)){
                redirect($thisurl."?view=checkdecks&amp;id={$cm->id}");
            }
            include $CFG->dirroot.'/mod/flashcard/editview.php';
            break;
        case 'add' : 
            if (!has_capability('mod/flashcard:manage', $context)){
                redirect($thisurl."?view=checkdecks&amp;id={$cm->id}");
            }
            include $CFG->dirroot.'/mod/flashcard/addview.php';
            break;
        case 'freeplay' :
            include $CFG->dirroot.'/mod/flashcard/freeplayview.php';
            break;
        case 'play' :
            include $CFG->dirroot.'/mod/flashcard/playview.php';
            break;
        default :
            include $CFG->dirroot.'/mod/flashcard/checkview.php';
    }

/// Finish the page
    echo $OUTPUT->footer($course);
