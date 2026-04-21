<?php
class CacheTest extends \PHPUnit\Framework\TestCase {

    private $test_post_id;

    public function setUp(): void
    {
        // Make sure the cache should purge
        update_option('templio_auto_purge', 1, true);
        
        // Global variable that tells if the cache has been purged or not
        $GLOBALS['cache_purged'] = false;

        // Set above variable to true when cache purged...
        add_action('templ_cache_purged', function() {
            $GLOBALS['cache_purged'] = true;
        });
    }

    public function testThatCacheIsPurged():void
    {
        $this->test_post_id = wp_insert_post([
            'post_title' => 'Test post',
            'post_content' => 'Hello world',
            'post_status' => 'auto-draft',
        ]);

        // Check so that a new auto-draft didn't clear the cache
        $this->assertFalse($GLOBALS['cache_purged']);

        wp_update_post([
            'ID' => $this->test_post_id,
            'post_status' => 'publish',
        ]);

        // Check so that the cache is purged when the post has been published
        $this->assertTrue($GLOBALS['cache_purged']);
        
    }

    public function testLocalIp(): void
    {
        $templ_cache = new templioCache();
        $local_ip = $templ_cache->get_local_ip();
        $ip_address_or_false = filter_var($local_ip, FILTER_VALIDATE_IP);
        $this->assertTrue( boolval($ip_address_or_false) );
    }

    public function tearDown(): void
    {
        wp_delete_post($this->test_post_id, true);
    }

}