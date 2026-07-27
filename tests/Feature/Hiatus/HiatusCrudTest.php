<?php

declare(strict_types=1);

use App\Models\Hiatus;
use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

it('creates a hiatus', function (): void {
    $this->post(route('hiatuses.store'), [
        'started_at' => '2026-05-10',
        'ended_at'   => '2026-07-20',
        'note'       => 'Viagem longa',
    ])->assertRedirect();

    $this->assertDatabaseHas('hiatuses', [
        'user_id' => auth()->id(),
        'note'    => 'Viagem longa',
    ]);
});

it('creates an open-ended hiatus', function (): void {
    $this->post(route('hiatuses.store'), [
        'started_at' => '2026-07-01',
        'ended_at'   => null,
    ])->assertRedirect();

    $this->assertDatabaseHas('hiatuses', [
        'user_id'  => auth()->id(),
        'ended_at' => null,
    ]);
});

it('rejects ended_at before started_at', function (): void {
    $this->post(route('hiatuses.store'), [
        'started_at' => '2026-07-10',
        'ended_at'   => '2026-07-01',
    ])->assertSessionHasErrors('ended_at');
});

it('rejects a period overlapping an existing hiatus', function (string $startedAt, ?string $endedAt): void {
    Hiatus::factory()->create([
        'user_id'    => auth()->id(),
        'started_at' => '2026-05-01',
        'ended_at'   => '2026-05-31',
    ]);

    $this->post(route('hiatuses.store'), [
        'started_at' => $startedAt,
        'ended_at'   => $endedAt,
    ])->assertSessionHasErrors('started_at');
})->with([
    'fully inside'          => ['2026-05-10', '2026-05-20'],
    'wrapping the existing' => ['2026-04-20', '2026-06-10'],
    'partial at the start'  => ['2026-04-20', '2026-05-05'],
    'partial at the end'    => ['2026-05-25', '2026-06-10'],
    'open-ended new'        => ['2026-05-15', null],
]);

it('rejects any period when an open-ended hiatus exists after it starts', function (): void {
    Hiatus::factory()->open()->create([
        'user_id'    => auth()->id(),
        'started_at' => '2026-06-01',
    ]);

    $this->post(route('hiatuses.store'), [
        'started_at' => '2026-07-01',
        'ended_at'   => '2026-07-10',
    ])->assertSessionHasErrors('started_at');
});

it('allows a period that does not overlap', function (): void {
    Hiatus::factory()->create([
        'user_id'    => auth()->id(),
        'started_at' => '2026-05-01',
        'ended_at'   => '2026-05-31',
    ]);

    $this->post(route('hiatuses.store'), [
        'started_at' => '2026-06-01',
        'ended_at'   => '2026-06-30',
    ])->assertSessionDoesntHaveErrors();

    expect(Hiatus::query()->count())->toBe(2);
});

it("ignores other users' hiatuses in the overlap check", function (): void {
    Hiatus::factory()->create([
        'started_at' => '2026-05-01',
        'ended_at'   => '2026-05-31',
    ]);

    $this->post(route('hiatuses.store'), [
        'started_at' => '2026-05-10',
        'ended_at'   => '2026-05-20',
    ])->assertSessionDoesntHaveErrors();
});

it('updates own hiatus keeping the same period', function (): void {
    $hiatus = Hiatus::factory()->create([
        'user_id'    => auth()->id(),
        'started_at' => '2026-05-01',
        'ended_at'   => '2026-05-31',
    ]);

    $this->put(route('hiatuses.update', $hiatus), [
        'started_at' => '2026-05-01',
        'ended_at'   => '2026-05-31',
        'note'       => 'Atualizado',
    ])->assertSessionDoesntHaveErrors();

    $this->assertDatabaseHas('hiatuses', [
        'id'   => $hiatus->id,
        'note' => 'Atualizado',
    ]);
});

it('rejects updating into a period overlapping another hiatus', function (): void {
    Hiatus::factory()->create([
        'user_id'    => auth()->id(),
        'started_at' => '2026-05-01',
        'ended_at'   => '2026-05-31',
    ]);
    $hiatus = Hiatus::factory()->create([
        'user_id'    => auth()->id(),
        'started_at' => '2026-07-01',
        'ended_at'   => '2026-07-15',
    ]);

    $this->put(route('hiatuses.update', $hiatus), [
        'started_at' => '2026-05-15',
        'ended_at'   => '2026-07-15',
    ])->assertSessionHasErrors('started_at');
});

it("forbids updating someone else's hiatus", function (): void {
    $hiatus = Hiatus::factory()->create();

    $this->put(route('hiatuses.update', $hiatus), [
        'started_at' => '2026-05-01',
        'ended_at'   => '2026-05-31',
    ])->assertForbidden();
});

it('deletes own hiatus', function (): void {
    $hiatus = Hiatus::factory()->create(['user_id' => auth()->id()]);

    $this->delete(route('hiatuses.destroy', $hiatus))->assertRedirect();

    $this->assertDatabaseMissing('hiatuses', ['id' => $hiatus->id]);
});

it("forbids deleting someone else's hiatus", function (): void {
    $hiatus = Hiatus::factory()->create();

    $this->delete(route('hiatuses.destroy', $hiatus))->assertForbidden();
});

it('cascades delete when the user is deleted', function (): void {
    $hiatus = Hiatus::factory()->create(['user_id' => auth()->id()]);

    auth()->user()->delete();

    $this->assertDatabaseMissing('hiatuses', ['id' => $hiatus->id]);
});
