<?php

namespace App\Http\Controllers\Cook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ingredient;
use Illuminate\Support\Facades\Validator;

class IngredientController extends Controller
{
    /**
     * Display a listing of the ingredients.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $ingredients = Ingredient::orderBy('name')->get();
        return view('cook.ingredients', compact('ingredients'));
    }

    /**
     * Show the form for creating a new ingredient.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('cook.ingredients');
    }

    /**
     * Display the specified ingredient.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $ingredient = Ingredient::findOrFail($id);
            return response()->json([
                'success' => true,
                'ingredient' => $ingredient
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ingredient not found'
            ], 404);
        }
    }

    /**
     * Store a newly created ingredient in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'minimum_stock' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        Ingredient::create($request->all());

        return redirect()->route('cook.ingredients')
            ->with('success', 'Ingredient added successfully.');
    }

    /**
     * Update the specified ingredient in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'category' => 'required|string|max:100',
            'description' => 'nullable|string',
            'minimum_stock' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $ingredient = Ingredient::findOrFail($id);
        $ingredient->update($request->all());

        return redirect()->route('cook.ingredients')
            ->with('success', 'Ingredient updated successfully.');
    }

    /**
     * Remove the specified ingredient from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $ingredient = Ingredient::findOrFail($id);
        $ingredient->delete();

        return redirect()->route('cook.ingredients')
            ->with('success', 'Ingredient deleted successfully.');
    }
}
