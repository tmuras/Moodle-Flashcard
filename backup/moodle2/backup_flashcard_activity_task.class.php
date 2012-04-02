<?php
/**
 * @package mod-flashcard
 * @category mod
 * @author Tomasz Muras <nexor1984@gmail.com>
 */
require_once($CFG->dirroot . '/mod/flashcard/backup/moodle2/backup_flashcard_stepslib.php');

class backup_flashcard_activity_task extends backup_activity_task {

    protected function define_my_settings() {
        
    }

    protected function define_my_steps() {
        $this->add_step(new backup_flashcard_activity_structure_step('flashcard_structure', 'flashcard.xml'));
    }

    static public function encode_content_links($content) {
        global $CFG;

        return $content;
        
        $base = preg_quote($CFG->wwwroot . '/mod/flashcard', '#');

        $pattern = "#(" . $base . "\/index.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@flashcardINDEX*$2@$', $content);

        $pattern = "#(" . $base . "\/view.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@flashcardVIEWBYID*$2@$', $content);

        $pattern = "#(" . $base . "\/report.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@flashcardREPORT*$2@$', $content);

        $pattern = "#(" . $base . "\/edit.php\?id\=)([0-9]+)#";
        $content = preg_replace($pattern, '$@flashcardEDIT*$2@$', $content);

        return $content;
    }

}
