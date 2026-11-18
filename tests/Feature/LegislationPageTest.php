<?php

use App\Models\Legislation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Legislation::query()->delete();
});

test('empty legislation page shows empty state', function () {
    $this->get(route('legislacao.index'))
        ->assertOk()
        ->assertSee('Em breve, documentos e links de legislação estarão disponíveis aqui.');
});

test('legislation page lists only published items with author and both links', function () {
    $author = User::factory()->create(['name' => 'Publicador de Teste']);

    Legislation::create([
        'user_id' => $author->id,
        'title' => 'Lei publicada',
        'description' => 'Descrição da lei publicada.',
        'original_publication_date' => '2024-06-12',
        'source' => 'Diário Oficial da União',
        'file' => 'legislations/lei.pdf',
        'external_link' => 'https://example.org/lei',
        'published_at' => now(),
    ]);

    Legislation::create([
        'user_id' => $author->id,
        'title' => 'Lei em rascunho',
        'description' => 'Descrição do rascunho.',
        'file' => 'legislations/rascunho.pdf',
        'external_link' => 'https://example.org/rascunho',
    ]);

    $this->get('/legislacao')
        ->assertOk()
        ->assertSee('Lei publicada')
        ->assertSee('Publicador de Teste')
        ->assertSee('Data da legislação: 12/06/2024')
        ->assertSee('Fonte: Diário Oficial da União')
        ->assertSee('storage/legislations/lei.pdf')
        ->assertSee('https://example.org/lei')
        ->assertDontSee('Lei em rascunho');
});

test('future publication remains hidden until its publication time', function () {
    $author = User::factory()->create();
    Legislation::create([
        'user_id' => $author->id,
        'title' => 'Lei futura',
        'description' => 'Ainda não deve aparecer.',
        'file' => 'legislations/futura.pdf',
        'external_link' => 'https://example.org/futura',
        'published_at' => now()->addDay(),
    ]);

    $this->get(route('legislacao.index'))->assertOk()->assertDontSee('Lei futura');
});

test('legislations are ordered from newest to oldest publication', function () {
    $author = User::factory()->create();
    foreach ([['Lei antiga', 3], ['Lei intermediária', 2], ['Lei recente', 1]] as [$title, $daysAgo]) {
        Legislation::create([
            'user_id' => $author->id,
            'title' => $title,
            'description' => 'Descrição.',
            'file' => 'legislations/lei.pdf',
            'external_link' => 'https://example.org/lei',
            'published_at' => now()->subDays($daysAgo),
        ]);
    }

    $this->get(route('legislacao.index'))
        ->assertOk()
        ->assertSeeInOrder(['Lei recente', 'Lei intermediária', 'Lei antiga']);
});

test('public page paginates twelve legislations at a time', function () {
    $author = User::factory()->create();
    foreach (range(1, 13) as $number) {
        Legislation::create([
            'user_id' => $author->id,
            'title' => sprintf('Legislação item %02d', $number),
            'description' => 'Descrição.',
            'file' => 'legislations/lei.pdf',
            'external_link' => 'https://example.org/lei',
            'published_at' => now()->subMinutes($number),
        ]);
    }

    $this->get(route('legislacao.index'))
        ->assertOk()
        ->assertSee('Legislação item 01')
        ->assertSee('Legislação item 12')
        ->assertDontSee('Legislação item 13');

    $this->get(route('legislacao.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Legislação item 13')
        ->assertDontSee('Legislação item 01');
});

test('legacy legislation without source and original date still renders', function () {
    $author = User::factory()->create(['name' => 'Autor legado']);
    Legislation::create([
        'user_id' => $author->id,
        'title' => 'Lei legada',
        'description' => 'Registro anterior aos campos novos.',
        'file' => 'legislations/legada.pdf',
        'external_link' => 'https://example.org/legada',
        'published_at' => now(),
    ]);

    $this->get(route('legislacao.index'))
        ->assertOk()
        ->assertSee('Lei legada')
        ->assertSee('Autor legado');
});

test('long existing descriptions are shortened in cards without changing stored text', function () {
    $author = User::factory()->create();
    $description = str_repeat('Texto longo ', 30);
    $legislation = Legislation::create([
        'user_id' => $author->id,
        'title' => 'Lei longa',
        'description' => $description,
        'file' => 'legislations/lei.pdf',
        'external_link' => 'https://example.org/lei',
        'published_at' => now(),
    ]);

    $this->get('/legislacao')
        ->assertOk()
        ->assertSee(Str::limit($description, 200))
        ->assertDontSee($description);

    expect($legislation->fresh()->description)->toBe($description);
});
