<?php
    namespace App\Twig;

    class TwigExtension {
        public function unixtime() {
            return time();
        }
    }