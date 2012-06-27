<?php

/* @var $DB mysqli_native_moodle_database */
/* @var $OUTPUT core_renderer */
/* @var $PAGE moodle_page */
?>
<?php

/**
 * This view provides a way for editing questions
 * 
 * @package mod-flashcard
 * @category mod
 * @author Gustav Delius
 * @contributors Valery Fremaux
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
/* @var $OUTPUT core_renderer */

if (!defined('MOODLE_INTERNAL')) {
    error("Illegal direct access to this screen");
}

require_once('cardsedit_form.php');

if ($action != '') {
    $result = include "{$CFG->dirroot}/mod/flashcard/editview.controller.php";
}

$page = optional_param('page', 0, PARAM_INT);

$cardsnum = $DB->count_records('flashcard_deckdata', array('flashcardid' => $flashcard->id));
$form = new flashcard_cardsedit_form(null, array('context' => $context));
$fileoptions = array(
    'subdirs' => false,
    'maxfiles' => -1,
    'maxbytes' => 0,
);
if ($fromform = $form->get_data()) {
    foreach ($fromform->cardid as $k => $id) {
        if ($id) {
            //update
            $newcard = new object();
            $newcard->id = $id;
            /*
              $newcard->questiontext = $fromform->question[$k]['text'];
              $newcard->answertext = $fromform->answer[$k]['text'];
             */
            $savedquestion = file_save_draft_area_files($fromform->question[$k]['itemid'], $context->id,
                    'mod_flashcard', 'question', $newcard->id, $fileoptions, $fromform->question[$k]['text']);
            $newcard->questiontext = $savedquestion;

            $savedanswer = file_save_draft_area_files($fromform->answer[$k]['itemid'], $context->id, 'mod_flashcard',
                    'answer', $newcard->id, $fileoptions, $fromform->answer[$k]['text']);
            $newcard->answertext = $savedanswer;

            $newcard->flashcardid = $flashcard->id;
            $DB->update_record('flashcard_deckdata', $newcard);
        } elseif ($fromform->question[$k]['text'] || $fromform->answer[$k]['text']) {
            //insert new
            $newcard = new object();
            $newcard->answertext = '';
            $newcard->questiontext = '';
            $newcard->flashcardid = $flashcard->id;
            $newcard->id = $DB->insert_record('flashcard_deckdata', $newcard);
            
            $savedquestion = file_save_draft_area_files($fromform->question[$k]['itemid'], $context->id,
                    'mod_flashcard', 'question', $newcard->id, $fileoptions, $fromform->question[$k]['text']);
            $newcard->questiontext = $savedquestion;

            $savedanswer = file_save_draft_area_files($fromform->answer[$k]['itemid'], $context->id, 'mod_flashcard',
                    'answer', $newcard->id, $fileoptions, $fromform->answer[$k]['text']);
            $newcard->answertext = $savedanswer;

            $newcard->flashcardid = $flashcard->id;
            $DB->update_record('flashcard_deckdata', $newcard);
        }
    }
}

if ($fromform && isset($fromform->addmore)) {
    //empty page, redirect to add page
    $url = new moodle_url('view.php', array('a' => $flashcard->id, 'view' => 'add'));
    redirect($url);
} elseif ($fromform) {
    $url = new moodle_url('view.php', array('a' => $flashcard->id, 'view' => 'edit', 'page' => $page));
    redirect($url);
} else {
    $pagedata = flashcard_get_page($flashcard, $page);
}
echo $out;
$toform = new object();
$toform->question = $pagedata->question;
$toform->answer = $pagedata->answer;
$toform->cardid = $pagedata->id;
$toform->view = 'edit';
$toform->id = $cm->id;
$form->set_data($toform);
$form->display();

$url = new moodle_url('/mod/flashcard/view.php', array('a' => $flashcard->id, 'view' => 'edit'));
echo $OUTPUT->paging_bar($cardsnum, $page, FLASHCARD_CARDS_PER_PAGE, $url);
