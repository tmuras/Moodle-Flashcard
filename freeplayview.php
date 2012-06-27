<?php
/**
 * This view allows free playing with a deck
 * 
 * @package mod-flashcard
 * @category mod
 * @author Gustav Delius
 * @contributors Valery Fremaux
 */
// Security
if (!defined('MOODLE_INTERNAL')) {
    error("Illegal direct access to this screen");
}
echo $out;
$subquestions = $DB->get_records('flashcard_deckdata', array('flashcardid' => $flashcard->id));
if (empty($subquestions)) {
    notice(get_string('nosubquestions', 'flashcard'));
    return;
}
$subquestions = draw_rand_array($subquestions, count($subquestions));
?>

<script language="javascript">
    //<![CDATA[
    currentitem = 0;
    maxitems = <?php echo count($subquestions); ?>;
    remaining = maxitems;

    var qtype = "<?php echo $flashcard->questionsmediatype ?>";
    var atype = "<?php echo $flashcard->answersmediatype ?>";

    var cards = new Array(maxitems);
    for(i = 0 ; i < maxitems ; i++){
        cards[i] = true;
    }

    function clicked(type, item){
        document.getElementById(type + item).style.display = "none";
        if (type == 'f'){
            oldtype = 'b';
        } 
        else{
            oldtype = 'f';
        }
        document.getElementById(oldtype + item).style.display = "block";
        if (type == 'f'){
            if (atype > 2){
                alert('item ' + item);
                qtobj = document.getElementById('bell_b' + item);
                qtobj.SetControllerVisible(true);
            }
        }
        if (type == 'b'){
            if (qtype > 2){
                qtobj = document.getElementById('bell_f' + item);
                qtobj.SetControllerVisible(true);
            }
        }
    }

    function next()
    {
        document.getElementById('f' + currentitem).style.display = "none";
        document.getElementById('b' + currentitem).style.display = "none";
        do {
            currentitem++;
            if (currentitem >= maxitems) currentitem = 0;
        }
        while (cards[currentitem] != true){
            document.getElementById('f' + currentitem).style.display = "block";
            qtobj = document.getElementById('bell_f' + currentitem);
            qtobj.SetControllerVisible(true);
        }
    }
        
    function previous() {
        document.getElementById('f' + currentitem).style.display = "none";
        document.getElementById('b' + currentitem).style.display = "none";
        do {
            currentitem--;
            if(currentitem < 0) currentitem = maxitems - 1;
        }
        while (cards[currentitem] != true){
            document.getElementById('f' + currentitem).style.display = "block";
            qtobj = document.getElementById('bell_f' + currentitem);
            qtobj.SetControllerVisible(true);
        }
    }
      
    function remove(){
        remaining--;
        document.getElementById('remain').innerHTML = remaining;
        if (remaining == 0){
            document.getElementById('f' + currentitem).style.display = "none";
            document.getElementById('b' + currentitem).style.display = "none";
            document.getElementById('finished').style.display = "block";
            document.getElementById('next').disabled = true;
            document.getElementById('previous').disabled = true;
            document.getElementById('remove').disabled = true;
        }
        else{
            cards[currentitem] = false;
            next();
        }
    }
    //]]>
</script>

<p>
    <?php print_string('freeplayinstructions',
            'flashcard');
    ?>.
</p>
<table class="flashcard_board" width="100%">
    <tr>
        <td rowspan="6">
            <?php
            $i = 0;

            if ($flashcard->flipdeck) {
                // flip media types once
                $tmp = $flashcard->answersmediatype;
                $flashcard->answersmediatype = $flashcard->questionsmediatype;
                $flashcard->questionsmediatype = $tmp;
            }

            foreach ($subquestions as $subquestion) {
                echo '<center>';
                $divid = "f$i";
                $divstyle = ($i > 0) ? 'display:none' : '';
                echo "<div id=\"{$divid}\" style=\"{$divstyle}\" class=\"backside\"";
                echo " onclick=\"javascript:clicked('f', '{$i}')\">";

                if ($flashcard->flipdeck) {
                    // flip card side values
                    $tmp = $subquestion->answertext;
                    $subquestion->answertext = $subquestion->questiontext;
                    $subquestion->questiontext = $tmp;
                }
                ?>
                <table class="flashcard_question" width="100%" height="100%">
                    <tr>
                        <td align="center" valign="center">
                            <?php
                            $questiontext = file_rewrite_pluginfile_urls($subquestion->questiontext, 'pluginfile.php',
                                    $context->id, 'mod_flashcard', 'question', $subquestion->id);
                            $options = new stdClass();
                            $options->noclean = true;
                            $options->overflowdiv = true;

                            echo format_text($questiontext, FORMAT_HTML);
                            ?>
                        </td>
                    </tr>
                </table>
                </div>
                </center>
        <center>
            <?php
            echo "<div id=\"b{$i}\" style=\"display: none\" class=\"frontside\"";
            echo " onclick=\"javascript:clicked('b', '{$i}')\">";
            ?>
            <table class="flashcard_answer" width="100%" height="100%">
                <tr>
                    <td align="center" valign="center" style="">
                        <?php
                        $answertext = file_rewrite_pluginfile_urls($subquestion->answertext, 'pluginfile.php',
                                $context->id, 'mod_flashcard', 'answer', $subquestion->id);
                        $options = new stdClass();
                        $options->noclean = true;
                        $options->overflowdiv = true;

                        echo format_text($answertext, FORMAT_HTML);
                        ?>
                    </td>
                </tr>
            </table>
            </div>
        </center>
        <?php
        $i++;
    }
    ?>
    <center>
        <div id="finished" style="display: none;" class="finished">
            <table width="100%" height="100%">
                <tr>
                    <td align="center" valign="middle" class="emptyset">
                        <?php print_string('emptyset',
                                'flashcard');
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
        <p><?php print_string('cardsremaining',
                                'flashcard');
                        ?>: <span id="remain"><?php echo count($subquestions); ?></span></p>
    </td>
</tr>
<tr>
    <td width="200px">
        <input id="next" type="button" value="<?php print_string('next',
                                'flashcard')
                        ?>" onclick="javascript:next()" />
    </td>
</tr>
<tr>
    <td width="200px">
        <input id="previous" type="button" value="<?php print_string('previous',
                                'flashcard')
                        ?>" onclick="javascript:previous()" />
    </td>
</tr>
<tr>
    <td width="200px">
        <input id="remove" type="button" value="<?php print_string('removecard',
                                'flashcard')
                        ?>" onclick="javascript:remove()" />
    </td>
</tr>
<tr>
    <td width="200px">
        <input type="button" value="<?php print_string('reset',
                                'flashcard')
                        ?>" onclick="javascript:location.reload()" />
    </td>
</tr>
<tr>
    <td width="200px" align="center" colspan="2">
        <br/><a href="<?php echo $CFG->wwwroot ?>/course/view.php?id=<?php echo $course->id ?>"><?php print_string('backtocourse',
                                'flashcard')
                        ?></a>
    </td>
</tr>
</table>
