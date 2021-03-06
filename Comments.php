<?php
require_once 'JsonHandler.php';
require_once 'FileHandler.php';

class Comments {

    public static function getParam($key) {
        if (array_key_exists($key, filter_input_array(INPUT_GET))) {
            return filter_input(INPUT_GET, $key, FILTER_SANITIZE_STRING);
        }

        return false;
    }

    public static function checkForComments($articleId) {
        $comments = Comments::getComments($articleId);

        if (count($comments) > 0) {
            return $comments;
        }

        return [];
    }

    public static function getComments($articleId, $filterUnlocked = true) {
        $file = FileHandler::readFile(__DIR__ . '/' . $articleId . '.json');
        $comments = JsonHandler::readJson($file, false);

        if ($filterUnlocked === true) {
            $comments = Comments::filterUnlockedComments($comments);
        }

        return $comments;
    }

    private static function filterUnlockedComments($commentsRaw) {
        $comments = [];

        foreach ($commentsRaw as $comment) {
            if ($comment->unlocked == true) {
                array_push($comments, $comment);
            }
        }

        return $comments;
    }

    public static function getTimeElapsed($timestamp, $level = 6) {
        $date = new DateTime();
        $date->setTimestamp($timestamp);
        $date = $date->diff(new DateTime());

        $timeElapsed = json_decode($date->format('{"year":%y,"month":%m,"day":%d,"hour":%h,"minute":%i}'), true);

        if ($timeElapsed['year'] > 0 || $timeElapsed['month'] > 5) {
            return date("d.m.Y h:i", $timestamp);
        }

        $timeElapsed = array_filter($timeElapsed);
        $timeElapsed = array_slice($timeElapsed, 0, $level);

        $lastKey = key(array_slice($timeElapsed, -1, 1, true));
        $output = '';

        foreach ($timeElapsed as $timeUnit => $value) {
            if ($output) {
                $output .= $timeUnit != $lastKey ? ', ' : ' ' . 'and' . ' ';
            }

            $timeUnit .= $value > 1 ? 's' : '';
            $output .= $value . ' ' . $timeUnit;
        }

        return $output;
    }

}
