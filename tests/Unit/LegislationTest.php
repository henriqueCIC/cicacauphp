<?php

use App\Models\Legislation;
use Carbon\CarbonInterface;

uses(Tests\TestCase::class);

test('legislation converts its dates to date objects', function () {
    $legislation = new Legislation([
        'original_publication_date' => '2024-06-12',
        'published_at' => '2024-06-13 14:30:00',
    ]);

    expect($legislation->original_publication_date)->toBeInstanceOf(CarbonInterface::class)
        ->and($legislation->original_publication_date->format('Y-m-d'))->toBe('2024-06-12')
        ->and($legislation->published_at)->toBeInstanceOf(CarbonInterface::class)
        ->and($legislation->published_at->format('Y-m-d H:i:s'))->toBe('2024-06-13 14:30:00');
});
