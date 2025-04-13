<?php
/**
 * Load vendor files for the plugin update checker
 *
 * Note: This file has been modified to remove dependencies on PucReadmeParser and Parsedown
 * as they are not needed for the basic update checking functionality.
 */

// Define empty classes to prevent fatal errors if any code still tries to use them
if (!class_exists('PucReadmeParser', false)) {
    class PucReadmeParser {
        public function parse_readme_contents($content) {
            return array();
        }
    }
}

if (!class_exists('Parsedown', false)) {
    class Parsedown {
        public static function instance() {
            static $instance = null;
            if ($instance === null) {
                $instance = new self();
            }
            return $instance;
        }

        public function text($text) {
            return $text;
        }
    }
}
