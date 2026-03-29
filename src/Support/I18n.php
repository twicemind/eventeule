<?php

namespace EventEule\Support;

class I18n
{
    public function register(): void
    {
        add_action('init', [$this, 'load_textdomain']);
    }

    public function load_textdomain(): void
    {
        load_plugin_textdomain(
            'eventeule',
            false,
            dirname(plugin_basename(EVENTEULE_FILE)) . '/languages'
        );
    }
}