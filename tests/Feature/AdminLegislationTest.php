<?php

use App\Models\Legislation;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(DatabaseTransactions::class);

beforeEach(function () {
    Legislation::query()->delete();
});

test('superadmin can publish and remove a local legislation with its PDF', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'superadmin', 'is_admin' => true]);

    $this->actingAs($admin)
        ->post(route('admin.legislations.store'), legislationPayload(['publish_now' => '1']))
        ->assertRedirect(route('admin.legislations.index'))
        ->assertSessionHas('success');

    $legislation = Legislation::where('title', 'Lei de teste')->firstOrFail();
    expect($legislation->user_id)->toBe($admin->id);
    expect($legislation->published_at)->not->toBeNull();
    Storage::disk('public')->assertExists($legislation->file);
    $this->get('/legislacao')->assertOk()->assertSee('Lei de teste');

    $this->delete(route('admin.legislations.destroy', $legislation))
        ->assertRedirect(route('admin.legislations.index'));

    $this->assertDatabaseMissing('legislations', ['id' => $legislation->id]);
    Storage::disk('public')->assertMissing($legislation->file);
});

test('guest is redirected to login from every legislation admin route', function () {
    $author = User::factory()->create(['is_admin' => true]);
    $legislation = Legislation::create([
        ...legislationPayload(['file' => 'legislations/existing.pdf']),
        'user_id' => $author->id,
    ]);

    $this->get(route('admin.legislations.index'))->assertRedirect(route('login'));
    $this->get(route('admin.legislations.create'))->assertRedirect(route('login'));
    $this->post(route('admin.legislations.store'), [])->assertRedirect(route('login'));
    $this->get(route('admin.legislations.edit', $legislation))->assertRedirect(route('login'));
    $this->put(route('admin.legislations.update', $legislation), [])->assertRedirect(route('login'));
    $this->patch(route('admin.legislations.toggle-publish', $legislation))->assertRedirect(route('login'));
    $this->delete(route('admin.legislations.destroy', $legislation))->assertRedirect(route('login'));
});

test('user without admin access receives forbidden from legislation admin routes', function () {
    $user = User::factory()->create(['role' => 'publicador', 'is_admin' => false]);
    $legislation = Legislation::create([
        ...legislationPayload(['file' => 'legislations/existing.pdf']),
        'user_id' => $user->id,
    ]);

    $this->actingAs($user)->get(route('admin.legislations.index'))->assertForbidden();
    $this->get(route('admin.legislations.create'))->assertForbidden();
    $this->post(route('admin.legislations.store'), legislationPayload())->assertForbidden();
    $this->get(route('admin.legislations.edit', $legislation))->assertForbidden();
    $this->put(route('admin.legislations.update', $legislation), legislationPayload())->assertForbidden();
    $this->patch(route('admin.legislations.toggle-publish', $legislation))->assertForbidden();
    $this->delete(route('admin.legislations.destroy', $legislation))->assertForbidden();
});

test('every administrative role can open legislation form', function (string $role) {
    $user = User::factory()->create(['role' => $role, 'is_admin' => true]);

    $this->actingAs($user)
        ->get(route('admin.legislations.create'))
        ->assertOk()
        ->assertSee('Data original da publicação')
        ->assertSee('Fonte');
})->with(['superadmin', 'admin', 'publicador']);

test('required legislation fields are validated', function () {
    $publisher = User::factory()->create(['role' => 'publicador', 'is_admin' => true]);

    $this->actingAs($publisher)->post(route('admin.legislations.store'), [])
        ->assertSessionHasErrors(['title', 'description', 'original_publication_date', 'source', 'file', 'external_link']);
});

test('description accepts 200 characters and rejects 201', function () {
    Storage::fake('public');
    $publisher = User::factory()->create(['role' => 'publicador', 'is_admin' => true]);

    $this->actingAs($publisher)
        ->post(route('admin.legislations.store'), legislationPayload(['description' => str_repeat('a', 200)]))
        ->assertSessionDoesntHaveErrors('description');

    $this->post(route('admin.legislations.store'), legislationPayload(['description' => str_repeat('a', 201)]))
        ->assertSessionHasErrors(['description']);
});

test('title and source reject values longer than 255 characters', function () {
    $publisher = User::factory()->create(['role' => 'publicador', 'is_admin' => true]);

    $this->actingAs($publisher)
        ->post(route('admin.legislations.store'), legislationPayload([
            'title' => str_repeat('t', 256),
            'source' => str_repeat('s', 256),
        ]))
        ->assertSessionHasErrors(['title', 'source']);
});

test('invalid date and external link are rejected', function () {
    $publisher = User::factory()->create(['role' => 'publicador', 'is_admin' => true]);

    $this->actingAs($publisher)
        ->post(route('admin.legislations.store'), legislationPayload([
            'original_publication_date' => '31/02/2024',
            'external_link' => 'javascript:alert(1)',
        ]))
        ->assertSessionHasErrors(['original_publication_date', 'external_link']);
});

test('non PDF file and PDF larger than 10 MB are rejected', function () {
    $publisher = User::factory()->create(['role' => 'publicador', 'is_admin' => true]);

    $this->actingAs($publisher)
        ->post(route('admin.legislations.store'), legislationPayload([
            'file' => UploadedFile::fake()->create('documento.txt', 10, 'text/plain'),
        ]))
        ->assertSessionHasErrors('file');

    $this->post(route('admin.legislations.store'), legislationPayload([
        'file' => UploadedFile::fake()->create('grande.pdf', 10241, 'application/pdf'),
    ]))->assertSessionHasErrors('file');
});

test('authenticated user is always recorded as publisher', function () {
    Storage::fake('public');
    $publisher = User::factory()->create(['role' => 'publicador', 'is_admin' => true]);
    $otherUser = User::factory()->create(['role' => 'superadmin', 'is_admin' => true]);

    $this->actingAs($publisher)->post(route('admin.legislations.store'), legislationPayload([
        'user_id' => $otherUser->id,
    ]))->assertRedirect(route('admin.legislations.index'));

    expect(Legislation::firstOrFail()->user_id)->toBe($publisher->id);
});

test('unchecked publish option creates a draft hidden from public page', function () {
    Storage::fake('public');
    $publisher = User::factory()->create(['role' => 'publicador', 'is_admin' => true]);

    $this->actingAs($publisher)
        ->post(route('admin.legislations.store'), legislationPayload(['title' => 'Rascunho interno']))
        ->assertRedirect(route('admin.legislations.index'));

    expect(Legislation::firstOrFail()->published_at)->toBeNull();
    $this->get(route('legislacao.index'))->assertDontSee('Rascunho interno');
});

test('deleting one legislation preserves other record and PDF', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'superadmin', 'is_admin' => true]);
    Storage::disk('public')->put('legislations/primeira.pdf', 'primeira');
    Storage::disk('public')->put('legislations/segunda.pdf', 'segunda');
    $first = Legislation::create([...legislationPayload(['file' => 'legislations/primeira.pdf']), 'user_id' => $admin->id]);
    $second = Legislation::create([...legislationPayload(['title' => 'Segunda', 'file' => 'legislations/segunda.pdf']), 'user_id' => $admin->id]);

    $this->actingAs($admin)
        ->delete(route('admin.legislations.destroy', $first))
        ->assertRedirect(route('admin.legislations.index'));

    $this->assertDatabaseMissing('legislations', ['id' => $first->id]);
    $this->assertDatabaseHas('legislations', ['id' => $second->id]);
    Storage::disk('public')->assertMissing('legislations/primeira.pdf');
    Storage::disk('public')->assertExists('legislations/segunda.pdf');
});

test('admin can edit legislation without replacing its PDF or publisher', function () {
    Storage::fake('public');
    $publisher = User::factory()->create(['role' => 'publicador', 'is_admin' => true]);
    $admin = User::factory()->create(['role' => 'admin', 'is_admin' => true]);
    Storage::disk('public')->put('legislations/original.pdf', 'conteúdo original');
    $legislation = Legislation::create([
        ...legislationPayload(['file' => 'legislations/original.pdf']),
        'user_id' => $publisher->id,
        'published_at' => now(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.legislations.edit', $legislation))
        ->assertOk()
        ->assertSee('Editar legislação')
        ->assertSee('Lei de teste');

    $this->put(route('admin.legislations.update', $legislation), legislationPayload([
        'title' => 'Lei atualizada',
        'description' => 'Descrição atualizada.',
        'file' => null,
    ]))->assertRedirect(route('admin.legislations.index'));

    $legislation->refresh();
    expect($legislation->title)->toBe('Lei atualizada')
        ->and($legislation->description)->toBe('Descrição atualizada.')
        ->and($legislation->file)->toBe('legislations/original.pdf')
        ->and($legislation->user_id)->toBe($publisher->id);
    Storage::disk('public')->assertExists('legislations/original.pdf');
});

test('replacing legislation PDF removes the previous file', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => 'superadmin', 'is_admin' => true]);
    Storage::disk('public')->put('legislations/original.pdf', 'conteúdo original');
    $legislation = Legislation::create([
        ...legislationPayload(['file' => 'legislations/original.pdf']),
        'user_id' => $admin->id,
    ]);

    $this->actingAs($admin)
        ->put(route('admin.legislations.update', $legislation), legislationPayload([
            'file' => UploadedFile::fake()->create('substituta.pdf', 100, 'application/pdf'),
        ]))->assertRedirect(route('admin.legislations.index'));

    $legislation->refresh();
    expect($legislation->file)->not->toBe('legislations/original.pdf');
    Storage::disk('public')->assertMissing('legislations/original.pdf');
    Storage::disk('public')->assertExists($legislation->file);
});

test('admin can publish a draft and later return it to draft', function () {
    $admin = User::factory()->create(['role' => 'superadmin', 'is_admin' => true]);
    $legislation = Legislation::create([
        ...legislationPayload(['file' => 'legislations/lei.pdf']),
        'user_id' => $admin->id,
        'published_at' => null,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.legislations.toggle-publish', $legislation))
        ->assertRedirect(route('admin.legislations.index'));
    expect($legislation->fresh()->published_at)->not->toBeNull();
    $this->get(route('legislacao.index'))->assertSee('Lei de teste');

    $this->patch(route('admin.legislations.toggle-publish', $legislation))
        ->assertRedirect(route('admin.legislations.index'));
    expect($legislation->fresh()->published_at)->toBeNull();
    $this->get(route('legislacao.index'))->assertDontSee('Lei de teste');
});
