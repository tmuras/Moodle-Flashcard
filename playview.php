<?php
/**
 * This view allows playing with a deck
 * 
 * @package mod-flashcard
 * @category mod
 * @author Gustav Delius
 * @contributors Valery Fremaux
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version Moodle 2.0
 */
/* @var $OUTPUT core_renderer */

// Security
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); /// It must be included from a Moodle page.
}

echo $out;

// we need it in controller
$deck = required_param('deck', PARAM_INT);

if ($action != '') {
    include $CFG->dirroot . '/mod/flashcard/playview.controller.php';
}

$subquestions = $DB->get_records('flashcard_deckdata', array('flashcardid' => $flashcard->id));
if (empty($subquestions)) {
    print_box_start();
    echo print_string('undefinedquestionset', 'flashcard');
    print_box_end();
    print_footer($course);
    return;
}

$consumed = explode(',', @$_SESSION['flashcard_consumed']);
$subquestions = array();
list($usql, $params) = $DB->get_in_or_equal($consumed, SQL_PARAMS_QM, 'param0000', false); // negative IN
$select = "
        flashcardid = {$flashcard->id} AND 
        userid = {$USER->id} AND 
        deck = {$deck} AND 
        id $usql
    ";
if ($cards = $DB->get_records_select('flashcard_card', $select, $params)) {
    foreach ($cards as $card) {
        $obj = new stdClass();
        $obj->entryid = $card->entryid;
        $obj->cardid = $card->id;
        $subquestions[] = $obj;
    }
} else {
    notice(get_string('nomorecards', 'flashcard'), $thisurl . "?view=checkdecks&amp;id={$cm->id}");
    redirect($thisurl . "?view=checkdecks&amp;id={$cm->id}");
}

/// randomize and get a question (obviously it is not a consumed question).

$random = rand(0, count($subquestions) - 1);
$subquestion = $DB->get_record('flashcard_deckdata', array('id' => $subquestions[$random]->entryid));

if ($flashcard->flipdeck) {
    // flip card side values
    $tmp = $subquestion->answertext;
    $subquestion->answertext = $subquestion->questiontext;
    $subquestion->questiontext = $tmp;
    // flip media types
    $tmp = $flashcard->answersmediatype;
    $flashcard->answersmediatype = $flashcard->questionsmediatype;
    $flashcard->questionsmediatype = $tmp;
}
?>

<script type="text/javascript">

    var qtype = "<?php echo $flashcard->questionsmediatype ?>";
    var atype = "<?php echo $flashcard->answersmediatype ?>";

    function togglecard(){
        var questionobj = document.getElementById("questiondiv");
        var answerobj = document.getElementById("answerdiv");
        if (questionobj.style.display == "none"){
            questionobj.style.display = "block";
	    
            // controls the quicktime player switching
            answerobj.style.display = "none";
            if (atype >= 2){
                bellobj = document.getElementById("bell_a");
                bellobj.Stop();
                bellobj.SetControllerVisible(false);
            }
            if (qtype >= 2){
                bellobj = document.getElementById("bell_q");
                bellobj.SetControllerVisible(true);
            }
        } else {
            questionobj.style.display = "none";
            answerobj.style.display = "block";

            // controls the quicktime player switching
            if (atype >= 2){
                bellobj = document.getElementById("bell_a");
                bellobj.SetControllerVisible(true);
            }
            if (qtype >= 2){
                bellobj = document.getElementById("bell_q");
                bellobj.Stop();
                bellobj.SetControllerVisible(false);
            }
        }
    }
</script>

<div id="flashcard_board" style="text-align: center;">
    <div id="flashcard_header">
        <?php echo $OUTPUT->heading($flashcard->name); ?>
        <p> <?php
        print_string('instructions', 'flashcard');
        ?></p>

    </div>
    <div id="questiondiv" style="border-style: dashed; width: 300px; margin: 10px auto 10px auto; padding: 10px 30px; display: block; " onclick="javascript:togglecard()" >
        <?php
        $questiontext = file_rewrite_pluginfile_urls($subquestion->questiontext, 'pluginfile.php', $context->id,
                'mod_flashcard', 'question', $subquestion->id);
        $textoptions = new stdClass();
        $textoptions->noclean = true;
        $textoptions->overflowdiv = true;

        echo format_text($questiontext, FORMAT_HTML, $textoptions);
        ?>
    </div>
    <div id="answerdiv" onclick="javascript:togglecard()" style="border-style: dashed; width: 300px; margin: 10px auto 10px auto; padding: 10px 30px; display: none;">
        <?php
        $answertext = file_rewrite_pluginfile_urls($subquestion->answertext, 'pluginfile.php', $context->id,
                'mod_flashcard', 'answer', $subquestion->id);
        $textoptions = new stdClass();
        $textoptions->noclean = true;
        $textoptions->overflowdiv = true;

        echo format_text($answertext, FORMAT_HTML, $textoptions);
        ?>
    </div>
    <div id="flashcard_controls">
        <p><?php print_string('cardsremaining', 'flashcard'); ?>: <span id="remain"><?php echo count($subquestions); ?></span></p>

        <?php
        $options['id'] = $cm->id;
        $options['what'] = 'igotit';
        $options['view'] = 'play';
        $options['deck'] = $deck;
        $options['cardid'] = $subquestions[$random]->cardid;
        echo $OUTPUT->single_button(new moodle_url('view.php', $options), get_string('igotit', 'flashcard'), 'post',
                array('class' => 'flashcard_playbutton'));
        ?>

        <?php
        $options['id'] = $cm->id;
        $options['what'] = 'ifailed';
        $options['view'] = 'play';
        $options['deck'] = $deck;
        $options['cardid'] = $subquestions[$random]->cardid;
        echo $OUTPUT->single_button(new moodle_url('view.php', $options), get_string('ifailed', 'flashcard'), 'post',
                array('class' => 'flashcard_playbutton'));
        ?>
        <br />
        <?php
        $options['id'] = $cm->id;
        $options['what'] = 'reset';
        $options['view'] = 'play';
        $options['deck'] = $deck;
        echo $OUTPUT->single_button(new moodle_url('view.php', $options), get_string('reset', 'flashcard'), 'post');
        ?>
        <br/><a href="<?php echo $thisurl ?>?id=<?php echo $cm->id ?>&amp;view=checkdecks"><?php
        print_string('backtodecks', 'flashcard')
        ?></a>
        - <a href="<?php echo $CFG->wwwroot ?>/course/view.php?id=<?php echo $course->id ?>"><?php
            print_string('backtocourse', 'flashcard')
        ?></a>
    </div>
</div>
