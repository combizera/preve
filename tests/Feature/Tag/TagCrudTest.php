<?php

declare(strict_types=1);

use App\Models\Tag;
use App\Models\User;

beforeEach(function (): void {
    $user = User::factory()->create();
    $this->actingAs($user);
});

// CREATE
it('should be able to create tag', function (): void {
    $response = $this->post(route('tags.store'), [
        'name'        => 'New Tag',
        'description' => 'This is a new tag',
    ]);

    $response->assertRedirect(route('tags.index'));

    $this->assertDatabaseHas('tags', [
        'name'        => 'New Tag',
        'description' => 'This is a new tag',
    ]);
});

// READ
it('should be able to view tags index', function (): void {
    $response = $this->get(route('tags.index'));

    $response->assertStatus(200);
});

// EDIT
it('should be able to edit tag', function (): void {
    $tag = Tag::factory()->create([
        'user_id' => auth()->id(),
        'name'    => 'New Tag',
    ]);

    $response = $this->put(route('tags.update', $tag->id), [
        'name' => 'Updated Tag',
    ]);

    $response->assertRedirect(route('tags.index'));

    $this->assertDatabaseHas('tags', [
        'name' => 'Updated Tag',
    ]);
});

it('shoud not be able to edit tag that you do not own', function (): void {
    $tag = Tag::factory()->create();

    $response = $this->put(route('tags.update', $tag->id), [
        'name' => 'Updated Tag',
    ]);

    $response->assertStatus(403);
});

// DELETE
it('should be able to delete tag', function (): void {
    $tag = Tag::factory()->create([
        'user_id' => auth()->id(),
    ]);

    $response = $this->delete(route('tags.destroy', $tag->id));

    $response->assertRedirect(route('tags.index'));
});

it('should not be able to delete tag that you do not own', function (): void {
    $tag = Tag::factory()->create();

    $response = $this->delete(route('tags.destroy', $tag->id));

    $response->assertStatus(403);
});
