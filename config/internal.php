<?php

return [
    'observability_emails' => array_values(array_filter(array_map(
        static fn (string $email): string => trim($email),
        explode(',', (string) env('OBSERVABILITY_EMAILS', '')),
    ))),
    'observability_roles' => ['Dueño', 'Owner'],
];
