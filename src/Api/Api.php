<?php

namespace EventEule\Api;

class Api
{
    public function register(): void
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    public function register_routes(): void
    {
        register_rest_route('eventeule/v1', '/ping', [
            'methods'  => 'GET',
            'callback' => [$this, 'ping'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function ping(): array
    {
        return [
            'success' => true,
            'plugin'  => 'EventEule',
            'version' => EVENTEULE_VERSION,
        ];
    }
}