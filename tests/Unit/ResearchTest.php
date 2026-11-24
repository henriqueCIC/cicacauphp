<?php

use App\Models\Research;

uses(Tests\TestCase::class);

test('research exposes the approved areas', function () {
    expect(Research::getAreas())->toBe([
        'agronomia' => 'Agronomia',
        'biotecnologia' => 'Biotecnologia',
        'economia_mercado' => 'Economia e Mercado',
        'fitossanidade' => 'Fitossanidade',
        'genetica_melhoramento' => 'Genética e Melhoramento',
        'meio_ambiente_sustentabilidade' => 'Meio Ambiente e Sustentabilidade',
        'producao_manejo' => 'Produção e Manejo',
        'tecnologia_alimentos' => 'Tecnologia de Alimentos',
        'outras' => 'Outras',
    ]);
});

test('research displays its area name or the legacy fallback', function () {
    $research = new Research(['area' => 'genetica_melhoramento']);
    $legacyResearch = new Research;

    expect($research->getAreaName())->toBe('Genética e Melhoramento')
        ->and($legacyResearch->getAreaName())->toBe('Sem área definida');
});
