<?php

namespace EventEule\Support;

class Deactivator
{
    public static function deactivate(): void
    {
        $timestamp = wp_next_scheduled('eventeule_generate_opening_events');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'eventeule_generate_opening_events');
        }

        flush_rewrite_rules();
    }
}