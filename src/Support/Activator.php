<?php

namespace EventEule\Support;

use EventEule\Domain\EventCategoryTaxonomy;
use EventEule\Domain\EventPostType;
use EventEule\Registration\RegistrationRepository;

class Activator
{
    public static function activate(): void
    {
        $postType = new EventPostType();
        $postType->register_post_type();

        $taxonomy = new EventCategoryTaxonomy();
        $taxonomy->register_taxonomy();

        RegistrationRepository::create_table();

        flush_rewrite_rules();
    }
}