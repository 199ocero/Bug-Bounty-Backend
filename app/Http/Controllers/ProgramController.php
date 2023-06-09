<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;

class ProgramController extends Controller
{
    public function index()
    {
        $programs = Program::latest()->with('user')->get();

        return response()->json([
            'data' => $programs,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|unique:programs',
            'pentesting_start_date' => 'required|date',
            'pentesting_end_date' => 'required|date',
        ]);

        $validatedData['user_id'] = $request->user()->id;

        $program = Program::create($validatedData);

        return response()->json([
            'message' => 'Program created successfully.',
            'data' => $program,
        ], 201);
    }

    public function show($id)
    {
        $program = Program::find($id);

        if (!$program) {
            return response()->json([
                'error' => 'Program not found.',
            ], 404);
        }

        $program->load([
            'user',
            'report' => function ($query) {
                $query->latest()->with('user');
            }
        ]);

        return response()->json([
            'data' => $program,
        ]);
    }

    public function showByUser(Request $request, $program_id)
    {
        $program = Program::where('id', $program_id)->where('user_id', $request->user()->id)->first();

        if (!$program) {
            return response()->json([
                'error' => 'Program not found in this user.',
            ], 404);
        }

        $program->load([
            'user',
            'report' => function ($query) {
                $query->latest()->with('user');
            }
        ]);

        return response()->json([
            'data' => $program,
        ]);
    }

    public function showAllByUser(Request $request)
    {
        $programs = Program::latest()->where('user_id', $request->user()->id)->get();

        return response()->json([
            'data' => $programs,
        ]);
    }

    public function update(Request $request, $id)
    {
        $program = Program::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$program) {
            return response()->json([
                'error' => 'Program not found in this user.',
            ], 404);
        }

        $validatedData = $request->validate([
            'name' => 'required|unique:programs,id,' . $id,
            'pentesting_start_date' => 'required|date',
            'pentesting_end_date' => 'required|date',
        ]);

        $program->update($validatedData);

        return response()->json([
            'message' => 'Program updated successfully.',
            'data' => $program,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $program = Program::where('id', $id)->where('user_id', $request->user()->id)->first();

        if (!$program) {
            return response()->json([
                'error' => 'Program not found in this user.',
            ], 404);
        }

        $program->delete();

        return response()->json([
            'message' => 'Program deleted successfully.',
        ]);
    }
}