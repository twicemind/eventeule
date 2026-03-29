<?php

namespace EventEule\Support;

use EventEule\Domain\EventCategoryTaxonomy;
use EventEule\Domain\EventPostType;

class Activator
{
    public static function activate(): void
    {
        $postType = new EventPostType();
        $postType->register_post_type();

        $taxonomy = new EventCategoryTaxonomy();
        $taxonomy->register_taxonomy();

        flush_rewrite_rules();
    }
}