<?php

namespace App\Http\Controllers;

use App\Models\Tester1;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Tester1Controller extends Controller
{
    public function index()
    {
        $items = Tester1::all();
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $request->validate([
        //    use the validation rules
        ]);

        $item = Tester1::create($request->all());
        return response()->json(['message' => 'Tester1 created successfully', 'data' => $item], 201);
    }

    public function show($id)
    {
        $item = Tester1::findOrFail($id);
        return response()->json($item);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            //    use the validation rules
        ]);

        $item = Tester1::findOrFail($id);
        $item->update($request->all());
        return response()->json(['message' => 'Tester1 updated successfully', 'data' => $item]);
    }

    public function destroy($id)
    {
        $item = Tester1::findOrFail($id);
        $item->delete();
        return response()->json(['message' => 'Tester1 deleted successfully']);
    }
}