<?php
/**
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the conditions of the BSD 3-Clause
 * License are met.
 *
 * You should have received a copy of the BSD 3-Clause License
 * along with QCaptcha. If not, see <https://choosealicense.com/licenses/bsd-3-clause/>.
 *
 * @package    QCaptcha
 * @author     Timo Kössler (https://timokoessler.de)
 * @since      1.0.1
 * @license    BSD-3-Clause
 * @copyright  Copyright (c) 2019, Timo Kössler
 * 
 * If you have any questions or feedback feel free to contact me:
 *  - E-Mail: info@timokoessler.de
 *  - Other stuff: https://timokoessler.de/contact/
 */

 
//Some options
define("qcaptcha_default_language", "en");
define("qcaptcha_existing_languages", array ('de', 'en', 'nl')); //If you added a language in the database you have to add it here


function is_session_started(){
    if(php_sapi_name() !== 'cli'){
        if(version_compare(phpversion(), '5.4.0', '>=')){
            return session_status() === PHP_SESSION_ACTIVE ? true : false;
        } else {
            return session_id() === '' ? false : true;
        }
    }
    return false;
}
if(is_session_started() === false ){
    session_start();
}

class QCaptcha {

    function __construct(){
        $db = $this->getdb();
        define('qcaptcha_lang', $this->getLanguage());
        define('qcaptcha_subtract_sign', $this->getBasic('subtract_sign'));
        define('qcaptcha_sum_sign', $this->getBasic('sum_sign'));
        define('qcaptcha_question_beginning', $this->getBasic('question_beginning'));
        define('qcaptcha_question_beginning_double', $this->getBasic('question_beginning_double'));
    }

    private function getdb(){
        return new SQLite3(__DIR__ . "/data/Questions.sqlite");
    }

    private function getNewQuestion(){
        $db = $this->getdb();
        $random = rand(1, 4);
        if($random <= 2){
            $id =  $db->querySingle('SELECT id FROM questions_' . qcaptcha_lang . ' ORDER BY RANDOM() LIMIT 1;');
            $question['question'] =  $db->querySingle('SELECT question FROM questions_' . qcaptcha_lang . ' WHERE id =\'' . $id . '\'');
            $question['answer'] =  $db->querySingle('SELECT answer FROM questions_' . qcaptcha_lang . ' WHERE id =\'' . $id . '\'');
            $db->close();
        } else if($random == 3){
            if(rand(1, 2) == 1){
                $number = rand(9, 20);
                $question['question'] = qcaptcha_question_beginning . " " . $this->num2text($number);
                $number2 = rand(1, 20);
                if($number2 > $number){
                    $number2 = $number2 - $number;
                }
                $question['question'] =  $question['question'] . " " . qcaptcha_subtract_sign . " " . $this->num2text($number2) . "?";
                $question['answer'] = $this->num2text($number - $number2);
            } else {
                $number = rand(2, 10);
                $question['question'] = qcaptcha_question_beginning . " " . $this->num2text($number);
                $number2 = rand(1, 10);
                $question['question'] =  $question['question'] . " " . qcaptcha_sum_sign . " " .  $this->num2text($number2) . "?";
                $question['answer'] = $this->num2text($number + $number2);
            }
        } else if($random == 4){
            $number = rand(2, 10);
            $question['question'] = qcaptcha_question_beginning_double . " " . $this->num2text($number) . "?";
            $question['answer'] = $this->num2text($number*2);
        }
        return $question;
    }

    private function getQuestion(){
        if(isset($_SESSION['qcaptcha_time']) && isset( $_SESSION['qcaptcha_question']) && isset($_SESSION['qcaptcha_answer'])){
            $lastDate = date_create_from_format('Y-m-d H:i:s', $_SESSION['qcaptcha_time']);
            $now = new DateTime();
            $interval = date_diff($lastDate, $now);
            $reference = new DateTimeImmutable;
            $endTime = $reference->add($interval);
            $secounds = $endTime->getTimestamp() - $reference->getTimestamp();

            if($secounds > 30){
                $question = $this->getNewQuestion();
                $_SESSION['qcaptcha_question'] = $question['question'];
                $_SESSION['qcaptcha_answer'] = $question['answer'];
                $_SESSION['qcaptcha_time'] = date('Y-m-d H:i:s');
            } else {
                $question['question'] =  $_SESSION['qcaptcha_question'];
                $question['answer'] = $_SESSION['qcaptcha_answer'];
            }
        } else {
            $question = $this->getNewQuestion();
            $_SESSION['qcaptcha_question'] = $question['question'];
            $_SESSION['qcaptcha_answer'] = $question['answer'];
            $_SESSION['qcaptcha_time'] = date('Y-m-d H:i:s');
        }
        return $question;
    }

    private function num2text($number){
        $db = $this->getdb();
        return $db->querySingle('SELECT name FROM numbers_' . qcaptcha_lang . ' WHERE id = \'' . $number . '\'');
    }

    private function getLanguage(){
        $array = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $language = qcaptcha_default_language;
        foreach($array as $lang){
            if(strpos($lang, ';')){
                $parts = explode(';', $lang);
                $lang = $parts[0];
            }
            if(strpos($lang, '-')){
                $parts = explode('-', $lang);
                $lang = $parts[0];
            }
            if(strpos($lang, '_')){
                $parts = explode('_', $lang);
                $lang = $parts[0];
            }
            if(in_array($lang, qcaptcha_existing_languages)){
                $language = $lang;
                break;
            }
        }
        return $language;
    }

    private function getBasic($basic){
        $db = $this->getdb();
        return $db->querySingle('SELECT value FROM basic_' . qcaptcha_lang . ' WHERE key = \'' . $basic . '\'');
    }

    public function build($theme = "modern"){
        $question = $this->getQuestion();

        if($theme == "modern"){
            echo '<div class="qcaptcha">
            <div class="qcaptcha-left">
                <img src="' . str_replace($_SERVER['DOCUMENT_ROOT'], "", __DIR__) . '/img/qcaptcha.png" tabindex="0" aria-label="' . $this->getBasic("captcha_title") . '" title="' . $this->getBasic("roboter_image") . '">
            </div>
            <div class="qcaptcha-right">
                <div class="qcaptcha-question" tabindex="0">' . htmlspecialchars($question['question']) . '</div>
                <label for="qcaptcha">' . $this->getBasic("answer_label") . '</label>
                <input type="text" autocomplete="off" class="qcaptcha-answer" name="qcaptcha" id="qcaptcha" placeholder="' . $this->getBasic("answer_box") . '" required>
            </div>
            </div>';
        } else if($theme == "classic") {
            echo '<div class="qcaptcha qcaptcha-classic">
            <div class="qcaptcha-left">
                <img src="' . str_replace($_SERVER['DOCUMENT_ROOT'], "", __DIR__) . '/img/qcaptcha.png" tabindex="0" aria-label="' . $this->getBasic("captcha_title") . '" title="' . $this->getBasic("roboter_image") . '">
            </div>
            <div class="qcaptcha-right">
                <div class="qcaptcha-question" tabindex="0">' . htmlspecialchars($question['question']) . '</div>
                <label for="qcaptcha">' . $this->getBasic("answer_label") . '</label>
                <input type="text" autocomplete="off" class="qcaptcha-answer" name="qcaptcha" id="qcaptcha" placeholder="' . $this->getBasic("answer_box") . '" required>
            </div>
            </div>';
        }
    }

    public function isValid(){
        if(!isset($_SESSION['qcaptcha_answer']) || empty($_SESSION['qcaptcha_answer'])){
            return false;
        }
        if(!isset($_POST['qcaptcha']) || empty($_POST['qcaptcha'])){
            return false;
        }
        if(strpos($_SESSION['qcaptcha_answer'], ';')){
            $parts = explode(';', $_SESSION['qcaptcha_answer']);
            $found = 0;
            foreach ($parts as $part) { 
                if(strcasecmp($_POST['qcaptcha'], strip_tags(trim($part))) == 0){
                    $found = 1;
                }
            }
            if($found == 1){
               unset($_SESSION['qcaptcha_answer']);
               unset($_SESSION['qcaptcha_time']);
               unset($_SESSION['qcaptcha_question']);
               return true;
            } else {
                unset($_SESSION['qcaptcha_answer']);
                unset($_SESSION['qcaptcha_time']);
                unset($_SESSION['qcaptcha_question']);
               return false;
            }
        } else {
            if(strcasecmp($_POST['qcaptcha'], strip_tags(trim($_SESSION['qcaptcha_answer']))) == 0){
                unset($_SESSION['qcaptcha_answer']);
                unset($_SESSION['qcaptcha_time']);
                unset($_SESSION['qcaptcha_question']);
                return true;
            } else {
                unset($_SESSION['qcaptcha_answer']);
                unset($_SESSION['qcaptcha_time']);
                unset($_SESSION['qcaptcha_question']);
                return false;
            }
        }
    }

    public function isInputValid($input){
        if(!isset($_SESSION['qcaptcha_answer']) || empty($_SESSION['qcaptcha_answer'])){
            return false;
        }
        if(!isset($input) || empty($input)){
            return false;
        }
        if(strpos($_SESSION['qcaptcha_answer'], ';')){
            $parts = explode(';', $_SESSION['qcaptcha_answer']);
            $found = 0;
            foreach ($parts as $part) { 
                if(strcasecmp($input, strip_tags(trim($part))) == 0){
                    $found = 1;
                }
            }
            if($found == 1){
                unset($_SESSION['qcaptcha_answer']);
                unset($_SESSION['qcaptcha_time']);
                unset($_SESSION['qcaptcha_question']);
               return true;
            } else {
               return false;
            }
        } else {
            if(strcasecmp($input, strip_tags(trim($_SESSION['qcaptcha_answer']))) == 0){
                unset($_SESSION['qcaptcha_answer']);
                unset($_SESSION['qcaptcha_time']);
                unset($_SESSION['qcaptcha_question']);
                return true;
            } else {
                return false;
            }
        }
    }
}

?>