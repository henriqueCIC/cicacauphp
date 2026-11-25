<?php

use App\Models\Research;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->withoutVite();
});

test('publisher sees the approved areas in create and edit forms', function () {
    $publisher = researchPublisher();
    $research = Research::create([
        ...researchPayload(),
        'user_id' => $publisher->id,
        'area' => null,
    ]);

    $this->actingAs($publisher)
        ->get(route('admin.researches.create'))
        ->assertOk()
        ->assertSee('Selecione uma área')
        ->assertSee('Meio Ambiente e Sustentabilidade');

    $this->get(route('admin.researches.edit', $research))
        ->assertOk()
        ->assertSee('Sem área definida')
        ->assertSee('Meio Ambiente e Sustentabilidade');
});

test('publisher creates a research with an approved area', function () {
    $publisher = researchPublisher();

    $this->actingAs($publisher)
        ->post(route('admin.researches.store'), researchPayload(['area' => 'agronomia']))
        ->assertRedirect(route('admin.researches.index'))
        ->assertSessionDoesntHaveErrors();

    $this->assertDatabaseHas('researches', [
        'title' => 'Pesquisa de teste',
        'area' => 'agronomia',
        'user_id' => $publisher->id,
    ]);
});

test('area is required when creating a research', function () {
    $publisher = researchPublisher();

    $this->actingAs($publisher)
        ->post(route('admin.researches.store'), researchPayload(['area' => null]))
        ->assertSessionHasErrors(['area']);
});

test('unknown area is rejected when creating or editing a research', function () {
    $publisher = researchPublisher();
    $research = Research::create([
        ...researchPayload(),
        'user_id' => $publisher->id,
        'area' => null,
    ]);

    $this->actingAs($publisher)
        ->post(route('admin.researches.store'), researchPayload(['area' => 'area_inexistente']))
        ->assertSessionHasErrors(['area']);

    $this->put(route('admin.researches.update', $research), researchPayload(['area' => 'area_inexistente']))
        ->assertSessionHasErrors(['area']);
});

test('publisher classifies a legacy research while editing it', function () {
    $publisher = researchPublisher();
    $research = Research::create([
        ...researchPayload(),
        'user_id' => $publisher->id,
        'area' => null,
    ]);

    $this->actingAs($publisher)
        ->put(route('admin.researches.update', $research), researchPayload([
            'title' => 'Pesquisa atualizada',
            'area' => 'tecnologia_alimentos',
        ]))
        ->assertRedirect(route('admin.researches.index'))
        ->assertSessionDoesntHaveErrors();

    expect($research->fresh()->area)->toBe('tecnologia_alimentos');
});

function researchPublisher(): User
{
    return User::factory()->create(['role' => 'publicador', 'is_admin' => true]);
}

function researchPayload(array $overrides = []): array
{
    return array_merge([
        'title' => 'Pesquisa de teste',
        'type' => 'artigo',
        'area' => 'agronomia',
        'summary' => 'Resumo da pesquisa.',
        'authors' => 'Autor de teste',
    ], $overrides);
}
