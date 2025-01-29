<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = $request->validate([
            'status' => 'string|in:pending,completed'
        ]);

        $todos = Todo::where('user_id', auth()->id())
        ->orderBy('status', 'asc')
        ->get();

        return response([
            'todos' => $todos
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fields = $request->validate([
            'title' => 'required|string'
        ]);

        $todo = Todo::create([
            'user_id' => auth()->id(),
            'title' => $fields['title'],
            'status' => 'pending'
        ]);

        return response([
            'todo' => $todo
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Todo $todo)
    {
        return response([
            'todo' => $todo
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Todo $todo)
    {
        $fields = $request->validate([
            'title' => 'required|string'
        ]);

        $todo->update([
            'title' => $fields['title']
        ]);

        return response([
            'todo' => $todo
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo)
    {
        $todo->delete();

        return response([
            'message' => 'Todo deleted successfully'
        ]);
    }

    public function updateStatus(Request $request, Todo $todo)
    {
        $fields = $request->validate([
            'status' => 'required|in:pending,completed'
        ]);

        $todo->update([
            'status' => $fields['status']
        ]);

        return response([
            'todo' => $todo
        ]);
    }

    public function redo(Todo $todo)
    {
        $todo->update([
            'status' => 'pending'
        ]);

        return response([
            'todo' => $todo
        ]);
    }

    public function getSummary() {
        $pending = Todo::where('user_id', auth()->id())
        ->where('status', 'pending')
        ->count();

        $completed = Todo::where('user_id', auth()->id())
        ->where('status', 'completed')
        ->count();

        return response([
            'pending' => $pending,
            'completed' => $completed
        ]);
    }
}
