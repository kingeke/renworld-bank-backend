<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test if users can fetch their profile
     *
     * @return void
     */
    public function testUsersCanFetchProfile()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'users')->get('/api/profile')->assertJson([
            'status' => 'success',
            'user' => $user->toArray()
        ])->assertStatus(200);
    }

    /**
     * Test if users can update their profile
     *
     * @return void
     */
    public function testUsersCanUpdateProfile()
    {
        $user = factory(User::class)->create();

        $data = $this->data();

        $this->actingAs($user, 'users')->put('/api/profile/update-profile', $data)->assertJson([
            'status' => 'success',
            'message' => 'Profile updated successfully'
        ])->assertStatus(200);

        $this->assertDatabaseHas('users', ['name' => $data['name'], 'email' => $data['email']]);
    }

    /**
     * Test if profile validations are accurate
     *
     * @return void
     */
    public function testProfileValidations()
    {
        $user = factory(User::class)->create();

        $dummyUser = factory(User::class)->create();

        $data = $this->data();

        $this->actingAs($user, 'users')->put('/api/profile/update-profile', array_merge($data, ['email' => '']))->assertJson([
            'status' => 'error',
            'message' => 'The email field is required.'
        ])->assertStatus(422);

        $this->actingAs($user, 'users')->put('/api/profile/update-profile', array_merge($data, ['email' => Str::random(2)]))->assertJson([
            'status' => 'error',
            'message' => 'The email must be a valid email address.'
        ])->assertStatus(422);

        $this->actingAs($user, 'users')->put('/api/profile/update-profile', array_merge($data, ['email' => $dummyUser->email]))->assertJson([
            'status' => 'error',
            'message' => 'The email has already been taken.'
        ])->assertStatus(422);

        $this->actingAs($user, 'users')->put('/api/profile/update-profile', array_merge($data, ['name' => '']))->assertJson([
            'status' => 'error',
            'message' => 'The name field is required.'
        ])->assertStatus(422);
    }

    /**
     * Test if users can change their password
     *
     * @return void
     */
    public function testUsersCanChangePassword()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'users')->put('/api/profile/change-password', [
            'currentPassword' => 'password',
            'password' => 'password',
            'password_confirmation' => 'password'
        ])->assertJson([
            'status' => 'success',
            'message' => 'Password changed successfully.'
        ])->assertStatus(200);
    }

    /**
     * Test if change password request validations are accurate
     *
     * @return void
     */
    public function testChangePasswordValidations()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'users')->put('/api/profile/change-password',  ['currentPassword' => ''])->assertJson([
            'status' => 'error',
            'message' => 'The current password field is required.'
        ])->assertStatus(422);

        $this->actingAs($user, 'users')->put('/api/profile/change-password',  [
            'currentPassword' => Str::random(2)
        ])->assertJson([
            'status' => 'error',
            'message' => 'The current password must be at least 6 characters.'
        ])->assertStatus(422);

        $this->actingAs($user, 'users')->put('/api/profile/change-password',  [
            'currentPassword' => Str::random(10),
            'password' => ''
        ])->assertJson([
            'status' => 'error',
            'message' => 'The password field is required.'
        ])->assertStatus(422);

        $this->actingAs($user, 'users')->put('/api/profile/change-password',  [
            'currentPassword' => Str::random(10),
            'password' => Str::random(2)
        ])->assertJson([
            'status' => 'error',
            'message' => 'The password must be at least 6 characters.'
        ])->assertStatus(422);

        $this->actingAs($user, 'users')->put('/api/profile/change-password',  [
            'currentPassword' => Str::random(10),
            'password' => Str::random(10),
            'password_confirmation' => ''
        ])->assertJson([
            'status' => 'error',
            'message' => 'The password confirmation does not match.'
        ])->assertStatus(422);
    }

    /**
     * Valid data for testing
     *
     * @return array
     */
    public function data()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->safeEmail
        ];
    }
}
