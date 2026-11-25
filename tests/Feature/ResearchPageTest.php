<?php

use App\Models\Research;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutVite();
});

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

test('research page shows every area and keeps unclassified research visible without filter', function () {
    $publisher = User::factory()->create();
    publicResearch($publisher, 'Pesquisa Agronomia', 'agronomia');
    publicResearch($publisher, 'Pesquisa Antiga', null);

    $this->get(route('pesquisa.index'))
        ->assertOk()
        ->assertSeeInOrder(['name="busca"', 'name="area"', 'name="tipo"'], false)
        ->assertSee('Meio Ambiente e Sustentabilidade')
        ->assertSee('Pesquisa Agronomia')
        ->assertSee('Pesquisa Antiga');
});

test('valid area filter returns only research from that area', function () {
    $publisher = User::factory()->create();
    publicResearch($publisher, 'Pesquisa Agronomia', 'agronomia');
    publicResearch($publisher, 'Pesquisa Biotecnologia', 'biotecnologia');
    publicResearch($publisher, 'Pesquisa Antiga', null);

    $this->get(route('pesquisa.index', ['area' => 'agronomia']))
        ->assertOk()
        ->assertSee('Pesquisa Agronomia')
        ->assertDontSee('Pesquisa Biotecnologia')
        ->assertDontSee('Pesquisa Antiga')
        ->assertSee('Limpar');
});

test('unknown area filter is ignored', function () {
    $publisher = User::factory()->create();
    publicResearch($publisher, 'Pesquisa Agronomia', 'agronomia');
    publicResearch($publisher, 'Pesquisa Antiga', null);

    $this->get(route('pesquisa.index', ['area' => 'area_inexistente']))
        ->assertOk()
        ->assertSee('Pesquisa Agronomia')
        ->assertSee('Pesquisa Antiga');
});

test('research cards and details display area labels including legacy fallback', function () {
    $publisher = User::factory()->create();
    $classified = publicResearch($publisher, 'Pesquisa Classificada', 'tecnologia_alimentos');
    $legacy = publicResearch($publisher, 'Pesquisa Legada', null);

    $this->get(route('pesquisa.index'))
        ->assertOk()
        ->assertSee('Tecnologia de Alimentos')
        ->assertSee('Sem área definida');

    $this->get(route('pesquisa.show', $classified->slug))
        ->assertOk()
        ->assertSee('Área')
        ->assertSee('Tecnologia de Alimentos');

    $this->get(route('pesquisa.show', $legacy->slug))
        ->assertOk()
        ->assertSee('Sem área definida');
});

test('research pagination preserves all filters', function () {
    $publisher = User::factory()->create();

    foreach (range(1, 13) as $number) {
        publicResearch($publisher, "Pesquisa Cacau {$number}", 'agronomia', 2025);
    }

    $this->get(route('pesquisa.index', [
        'busca' => 'Cacau',
        'area' => 'agronomia',
        'tipo' => 'artigo',
        'ano' => 2025,
    ]))
        ->assertOk()
        ->assertSee('busca=Cacau', false)
        ->assertSee('area=agronomia', false)
        ->assertSee('tipo=artigo', false)
        ->assertSee('ano=2025', false);
});

function publicResearch(User $publisher, string $title, ?string $area, ?int $year = null): Research
{
    return Research::create([
        'user_id' => $publisher->id,
        'title' => $title,
        'type' => 'artigo',
        'area' => $area,
        'summary' => 'Resumo da pesquisa.',
        'authors' => 'Autor de teste',
        'year' => $year,
        'published_at' => now(),
    ]);
}
