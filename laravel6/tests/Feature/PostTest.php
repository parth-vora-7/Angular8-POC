<?php

namespace Tests\Feature;

use App\Post;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * To authentic user using Passport before calling any API
     *
     * @return class
     */
    public function authenticUser()
    {
        Passport::actingAs(
            factory(User::class)->create(),
            ['create-servers']
        );
    }

    /**
     * To generate a post model using dummy values
     *
     * @return class
     */
    public function createPost()
    {
        return factory(Post::class)->make();
    }

    /**
     * Get all the posts
     *
     * @return void
     */
    public function testGetAllPosts()
    {
        $this->authenticUser();
        $post = $this->createPost()->toArray();
        $this->json('POST', '/api/posts', $post)->assertStatus(200);

        $post = $this->createPost()->toArray();
        $this->json('POST', '/api/posts', $post)->assertStatus(200);

        $this->json('GET', '/api/posts')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'success',
            ])
            ->assertJsonCount(2, 'data');
    }

    /**
     * Create a post with all fields
     *
     * @return void
     */
    public function testCreatePostWithAllFields()
    {
        $this->authenticUser();
        $post = $this->createPost()->toArray();

        $this->json('POST', '/api/posts', $post)
            ->assertStatus(200);
    }

    /**
     * Create a post without title
     *
     * @return void
     */
    public function testCreatePostWithoutTitle()
    {
        $this->authenticUser();
        $post = $this->createPost()->toArray();
        unset($post['title']);

        $this->json('POST', '/api/posts', $post)
            ->assertStatus(422)
            ->assertSeeText('The title field is required.');
    }

    /**
     * Create a post without content
     *
     * @return void
     */
    public function testCreatePostWithoutContent()
    {
        $this->authenticUser();
        $post = $this->createPost()->toArray();
        unset($post['content']);

        $this->json('POST', '/api/posts', $post)
            ->assertStatus(422)
            ->assertSeeText('The content field is required.');
    }

    /**
     * Create a post with existing title
     *
     * @return void
     */
    public function testCreatePostWithExistingTitle()
    {
        $this->authenticUser();
        $post = $this->createPost()->toArray();
        $this->json('POST', '/api/posts', $post)->assertStatus(200);

        $this->json('POST', '/api/posts', $post)->assertStatus(422)
            ->assertSeeText('The title has already been taken.');
    }

    /**
     * Get an exist post
     *
     * @return void
     */
    public function testGetExistPost()
    {
        $this->authenticUser();
        $post = $this->createPost()->toArray();

        $post = $this->json('POST', '/api/posts', $post)->assertStatus(200);

        $this->json('GET', '/api/posts/' . $post->json('data.id'))->assertStatus(200);
    }

    /**
     * Get a non-exist post
     *
     * @return void
     */
    public function testGetNonExistPost()
    {
        $this->authenticUser();

        $this->json('GET', '/api/posts/10000')->assertStatus(404);
    }

    /**
     * Update a post
     *
     * @return void
     */
    public function testUpdatePost()
    {
        $this->authenticUser();
        $post = $this->createPost()->toArray();
        $post = $this->json('POST', '/api/posts', $post)
            ->assertStatus(200)
            ->json('data');

        $updatePost          = $post;
        $updatePost['title'] = 'Updated title';
        $updatePost          = $this->json('PATCH', '/api/posts/' . $post['id'], $updatePost)
            ->assertStatus(200)->json('data');

        $this->json('GET', '/api/posts/' . $post['id'])
            ->assertStatus(200)
            ->assertSeeText($updatePost['title']);
    }

    /**
     * Delete a post
     *
     * @return void
     */
    public function testDeletePost()
    {
        $this->authenticUser();
        $post = $this->createPost()->toArray();
        $post = $this->json('POST', '/api/posts', $post)
            ->assertStatus(200)
            ->json('data');

        $this->json('DELETE', '/api/posts/' . $post['id'])
            ->assertStatus(200)->json('data');

        $this->json('GET', '/api/posts/' . $post['id'])
            ->assertStatus(404);
    }
}
