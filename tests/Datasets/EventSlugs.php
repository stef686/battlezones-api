<?php

/**
 * Slugs no Event may be created at, and the words each is refused with. Shared
 * so the create and the availability check are held to one list.
 */
dataset('refused event slugs', [
    'upper case' => ['Croydon', 'lowercase letters, numbers and single hyphens'],
    'spaces' => ['croydon doubles', 'lowercase letters, numbers and single hyphens'],
    'leading hyphen' => ['-croydon', 'lowercase letters, numbers and single hyphens'],
    'trailing hyphen' => ['croydon-', 'lowercase letters, numbers and single hyphens'],
    'doubled hyphen' => ['croydon--doubles', 'lowercase letters, numbers and single hyphens'],
    'only hyphens' => ['---', 'lowercase letters, numbers and single hyphens'],
    'too short' => ['cd', 'between 3 and 60'],
    'too long' => [str_repeat('a', 61), 'between 3 and 60'],
    'reserved by the create wizard' => ['new', 'reserved'],
    'reserved for the spa' => ['create', 'reserved'],
]);
