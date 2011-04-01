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

    // Security
    if (!defined('MOODLE_INTERNAL')){
        die('Direct access to this script is forbidden.'); /// It must be included from a Moodle page.
    }

    // we need it in controller
    $deck = required_param('deck', PARAM_INT);

    if ($action != ''){
        include $CFG->dirroot.'/mod/flashcard/playview.controller.php';
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
	if ($cards = $DB->get_records_select('flashcard_card', $select, $params)){
    	foreach($cards as $card){
    	    $obj = new stdClass();
    	    $obj->entryid = $card->entryid;
    	    $obj->cardid = $card->id;
    	    $subquestions[] = $obj;
    	}
    } else {
        notice(get_string('nomorecards', 'flashcard'), $thisurl."?view=checkdecks&amp;id={$cm->id}");
        redirect($thisurl."?view=checkdecks&amp;id={$cm->id}");
    }
    
/// randomize and get a question (obviously it is not a consumed question).
    
    $random = rand(0, count($subquestions) - 1);
    $subquestion = $DB->get_record('flashcard_deckdata', array('id' => $subquestions[$random]->entryid));

    if ($flashcard->flipdeck){
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

<p>
<?php 
print_heading($flashcard->name); 
print_string('instructions', 'flashcard'); 
?>
</p>
<table class="flashcard_board" width="100%">
    <tr>
        <td rowspan="5">
        <center>

            <div id="questiondiv" style="display: block" class="backside" onclick="javascript:togglecard()">
            <table class="flashcard_question" width="100%" height="100%">
                <tr>
                    <td align="center" valign="center">
                        <?php
                        if ($flashcard->questionsmediatype == FLASHCARD_MEDIA_IMAGE) {
                            flashcard_print_image($flashcard, $subquestion->questiontext);
                        } elseif ($flashcard->questionsmediatype == FLASHCARD_MEDIA_SOUND){                            
                            flashcard_play_sound($flashcard, $subquestion->questiontext, 'false', false, 'bell_q');
                        } elseif ($flashcard->questionsmediatype == FLASHCARD_MEDIA_IMAGE_AND_SOUND){                            
                            list($image, $sound) = split('@', $subquestion->questiontext);
                            flashcard_print_image($flashcard, $image);
                            echo "<br/>";
                            flashcard_play_sound($flashcard, $sound, 'false', false, 'bell_q');
                        } else {
                            echo format_string($subquestion->questiontext);
                        }
                        ?>
                    </td>
                </tr>
            </table>
            </div>

            <div id="answerdiv" style="display: none" class="frontside" onclick="javascript:togglecard()">
    		<table class="flashcard_answer" width="100%" height="100%">
    		    <tr>
    		        <td align="center" valign="center">
    		            <?php 
                        if ($flashcard->answersmediatype == FLASHCARD_MEDIA_IMAGE) {
                            flashcard_print_image($flashcard, $subquestion->answertext);
                        } elseif ($flashcard->answersmediatype == FLASHCARD_MEDIA_SOUND){                            
                            flashcard_play_sound($flashcard, $subquestion->answertext, 'false', false, 'bell_a');
                        } elseif ($flashcard->answersmediatype == FLASHCARD_MEDIA_IMAGE_AND_SOUND){                            
                            list($image, $sound) = split('@', $subquestion->answertext);
                            flashcard_print_image($flashcard, $image);
                            echo "<br/>";
                            flashcard_play_sound($flashcard, $sound, 'false', false, 'bell_a');
                        } else {
                            echo format_string($subquestion->answertext);
                        }
                        ?>
    		        </td>
    		    </tr>
    		</table>
    		</div>

    		</center>    
        </td>
    </tr>
    <tr>
        <td width="200px">
            <p><?php print_string('cardsremaining', 'flashcard'); ?>: <span id="remain"><?php echo count($subquestions);?></span></p>
        </td>
    </tr>
    <tr>
        <td>
            <?php 
            $options['id'] = $cm->id;
            $options['what'] = 'igotit';
            $options['view'] = 'play';
            $options['deck'] = $deck;
            $options['cardid'] = $subquestions[$random]->cardid;
            print_single_button('view.php', $options, get_string('igotit', 'flashcard')); 
            ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php 
            $options['id'] = $cm->id;
            $options['what'] = 'ifailed';
            $options['view'] = 'play';
            $options['deck'] = $deck;
            $options['cardid'] = $subquestions[$random]->cardid;
            print_single_button('view.php', $options, get_string('ifailed', 'flashcard')); 
            ?>
        </td>
    </tr>
    <tr>
        <td>
            <?php 
            $options['id'] = $cm->id;
            $options['what'] = 'reset';
            $options['view'] = 'play';
            $options['deck'] = $deck;
            print_single_button('view.php', $options, get_string('reset', 'flashcard')); 
            ?>
        </td>
    </tr>
    <tr>
        <td align="center" colspan="2">
            <br/><a href="<?php echo $thisurl ?>?id=<?php echo $cm->id ?>&amp;view=checkdecks"><?php print_string('backtodecks', 'flashcard') ?></a>
            - <a href="<?php echo $CFG->wwwroot ?>/course/view.php?id=<?php echo $course->id ?>"><?php print_string('backtocourse', 'flashcard') ?></a>
        </td>
      </tr>
</table>
