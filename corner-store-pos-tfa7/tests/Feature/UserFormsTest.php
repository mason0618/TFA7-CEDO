<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_forms_are_prefilled_and_the_edit_form_supports_uploads(): void
    {
        $user = User::factory()->create(['username' => 'existing.user', 'full_name' => 'Existing User']);
        $this->get('/users/create')->assertOk()->assertSee('name="_token"', false)->assertDontSee('name="avatar"', false);
        $this->get('/users/'.$user->id.'/edit')->assertOk()->assertSee('value="existing.user"', false)->assertSee('value="Existing User"', false)->assertSee('enctype="multipart/form-data"', false)->assertSee('name="avatar"', false)->assertSee('name="_method" value="PUT"', false);
    }

    public function test_a_user_can_be_created_and_cannot_supply_an_avatar_path(): void
    {
        $this->post('/users', ['username' => 'new.user', 'full_name' => 'New User', 'avatar' => 'avatars/untrusted.jpg'])
            ->assertRedirect('/users')->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['username' => 'new.user', 'full_name' => 'New User', 'avatar' => null]);
    }

    public function test_duplicate_and_required_fields_reject_creation_and_keep_old_input(): void
    {
        User::factory()->create(['username' => 'taken.user']);
        $this->from('/users/create')->post('/users', ['username' => 'taken.user', 'full_name' => 'Trying User'])
            ->assertRedirect('/users/create')->assertSessionHasErrors('username')->assertSessionHasInput('full_name', 'Trying User');
        $this->withCookie(config('session.cookie'), session()->getId())->get('/users/create')->assertOk()->assertSee('value="Trying User"', false)->assertSee('already been taken');
        $this->assertDatabaseCount('users', 1);
        $this->post('/users', ['username' => '', 'full_name' => ''])->assertSessionHasErrors(['username', 'full_name']);
    }

    public function test_a_user_can_keep_their_username_when_updating(): void
    {
        $user = User::factory()->create(['username' => 'own.username']);
        $this->put('/users/'.$user->id, ['username' => 'own.username', 'full_name' => 'Updated Name'])->assertRedirect('/users')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'username' => 'own.username', 'full_name' => 'Updated Name']);
    }

    public function test_another_users_username_is_rejected_and_old_input_is_shown(): void
    {
        $user = User::factory()->create(['username' => 'original.user', 'full_name' => 'Saved Name']);
        $other = User::factory()->create(['username' => 'taken.user']);
        $editUrl = '/users/'.$user->id.'/edit';
        $this->from($editUrl)->put('/users/'.$user->id, ['id' => $other->id, 'username' => 'taken.user', 'full_name' => 'Unsaved Name'])
            ->assertRedirect($editUrl)->assertSessionHasErrors('username');
        $this->withCookie(config('session.cookie'), session()->getId())->get($editUrl)->assertOk()->assertSee('value="Unsaved Name"', false)->assertSee('value="taken.user"', false);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'username' => 'original.user', 'full_name' => 'Saved Name']);
    }

    public function test_missing_users_and_non_string_usernames_are_rejected(): void
    {
        $this->get('/users/999999/edit')->assertNotFound();
        $this->post('/users', ['username' => ['invalid'], 'full_name' => 'Name'])->assertSessionHasErrors('username');
        $this->assertDatabaseCount('users', 0);
    }
}
