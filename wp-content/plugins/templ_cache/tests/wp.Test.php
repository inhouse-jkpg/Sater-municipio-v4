<?php
class WordPressTest extends \PHPUnit\Framework\TestCase {
    public function testThatWordPressIsLoaded() {
        $this->assertTrue(defined('ABSPATH'));
        $this->assertTrue(class_exists('templioCache'));
    }
}