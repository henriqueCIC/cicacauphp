<?php

use App\Models\Research;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

test('research page lists available years without conflicting publication order', function () {
    $publisher = User::factory()->create();

    foreach ([2024, 2025] as $year) {
        Research::create([
            'user_id' => $publisher->id,
            'title' => "Pesquisa {$year}",
            'type' => 'artigo',
            'summary' => 'Resumo da pesquisa.',
            'authors' => 'Autor de teste',
            'year' => $year,
            'published_at' => now(),
        ]);
    }

    $this->get(route('pesquisa.index'))
        ->assertOk()
        ->assertSeeInOrder(['2025', '2024']);
});
