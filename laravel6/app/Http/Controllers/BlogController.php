<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $posts = Post::all();
        return new JsonResponse(['success' => true, 'data' => $posts]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title'   => 'required|unique:posts|max:255',
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            return new JsonResponse(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $post            = new Post;
        $post->title     = request('title');
        $post->content   = request('content');
        $post->author_id = auth()->user()->id;
        if ($post->save()) {
            return new JsonResponse(['success' => true, 'data' => $post]);
        }
        return new JsonResponse(['success' => false]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Post $post)
    {
        return new JsonResponse(['success' => false, 'data' => $post]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Post  $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);

        $validator = Validator::make($request->all(), [
            'title'   => ['required', 'max:255', Rule::unique('posts')->ignore($post->id)],
            'content' => 'required',
        ]);
        if ($validator->fails()) {
            return new JsonResponse(['success' => false, 'errors' => $validator->errors()], 422);
        }
        $post->title     = request('title');
        $post->content   = request('content');
        $post->author_id = auth()->user()->id;
        if ($post->save()) {
            return new JsonResponse(['success' => true, 'data' => $post]);
        }
        return new JsonResponse(['success' => false]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post  $post
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        if ($post->delete()) {
            return new JsonResponse(['success' => false]);
        }
        return new JsonResponse(['success' => true]);
    }
}
