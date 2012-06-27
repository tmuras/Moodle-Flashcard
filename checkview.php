<?php
/**
 * This view allows checking deck states
 * 
 * @package mod-flashcard
 * @category mod
 * @author Gustav Delius
 * @contributors Valery Fremaux
 * @version Moodle 2.0
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
// security
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.'); /// It must be included from a Moodle page.
}
echo $out;
/// get available decks for user and calculate deck state

if (!$decks = flashcard_get_deck_status($flashcard)) {
    // if deck status have bever been initialized initialized them
    if (flashcard_initialize($flashcard, $USER->id)) {
        $decks = flashcard_get_deck_status($flashcard);
    } else {
        if (has_capability('mod/flashcard:manage', $context)) {
            $url = "view.php?id={$cm->id}&amp;view=edit";
        } else {
            $url = "{$CFG->wwwroot}/course/view.php?id={$course->id}";
        }
        notice(get_string('nocards', 'flashcard'), $url);
    }
}
?>
<center>
    <table width="90%" cellspacing="10">
        <tr>
            <th>
<?php print_string('difficultcards',
        'flashcard') ?>
            </th>
            <?php
            if ($flashcard->decks >= 3) {
                ?>
                <th>
                    <?php print_string('mediumeffortcards', 'flashcard') ?>
                </th>
                <?php
            }
            ?>
            <th>
                <?php print_string('easycards', 'flashcard') ?>
            </th>
            <?php
            if ($flashcard->decks >= 4) {
                ?>
                <th>
                    <?php print_string('trivialcards', 'flashcard') ?>
                </th>
                <?php
            }
            ?>
        </tr>
        <tr valign="top">
            <td>
                <?php
                print_string('cardsindeck', 'flashcard', $decks->decks[0]->count);
                echo "<br/>";
                if ($decks->decks[0]->count == 0) {
                    flashcard_print_deck($cm, 0);
                } else {
                    if ($decks->decks[0]->reactivate) {
                        flashcard_print_deck($cm, 1);
                    } else {
                        flashcard_print_deck($cm, -1);
                    }
                }
                ?>
            </td>
            <td>
                <?php
                print_string('cardsindeck', 'flashcard', $decks->decks[1]->count);
                echo "<br/>";
                if ($decks->decks[1]->count == 0) {
                    flashcard_print_deck($cm, 0);
                } else {
                    if ($decks->decks[1]->reactivate) {
                        flashcard_print_deck($cm, 2);
                    } else {
                        flashcard_print_deck($cm, -2);
                    }
                }
                ?>
            </td>
            <?php
            if ($flashcard->decks >= 3) {
                ?>
                <td>
                    <?php
                    print_string('cardsindeck', 'flashcard', $decks->decks[2]->count);
                    echo "<br/>";
                    if ($decks->decks[2]->count == 0) {
                        flashcard_print_deck($cm, 0);
                    } else {
                        if ($decks->decks[2]->reactivate) {
                            flashcard_print_deck($cm, 3);
                        } else {
                            flashcard_print_deck($cm, -3);
                        }
                    }
                    ?>
                </td>
                <?php
            }
            if ($flashcard->decks >= 4) {
                ?>
                <td>
                    <?php
                    print_string('cardsindeck', 'flashcard', $decks->decks[3]->count);
                    echo "<br/>";
                    if ($decks->decks[3]->count == 0) {
                        flashcard_print_deck($cm, 0);
                    } else {
                        if ($decks->decks[3]->reactivate) {
                            flashcard_print_deck($cm, 4);
                        } else {
                            flashcard_print_deck($cm, -4);
                        }
                    }
                    ?>
                </td>
                <?php
            }
            ?>
        </tr>
    </table>
</center>