<?php

return [
    'langs' => explode(',', env('ONTHISDAY_LANGS', 'zh,en')),
    'types' => explode(',', env('ONTHISDAY_TYPES', 'selected,births,deaths,events,holidays')),
    'cache_ttl_seconds' => (int) env('ONTHISDAY_CACHE_TTL', 86400),
    'max_items' => (int) env('ONTHISDAY_MAX_ITEMS', 10),
    'user_agent' => env('ONTHISDAY_UA', 'OnThisDayApp/1.0 (contact: mouselin00@gmail.com)'),
];
