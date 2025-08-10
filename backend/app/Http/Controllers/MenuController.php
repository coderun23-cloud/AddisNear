<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class MenuController extends Controller
{
      public static function middleware(){
        return[
            new Middleware('auth:sanctum',except:['index','show'])
        ];
     }
    public function index()
    {
        $menus = Menu::latest()->get();
        return response()->json($menus);
    }

  public function store(Request $request)
{
    Gate::authorize('menucreate', Menu::class);
    try {
        $request->validate([
            'place_id'    => 'required|exists:places,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $imagePaths = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('places', 'public');
                $imagePaths[] = $path;
            }
        }

        $menu = Menu::create([
            'place_id'     => $request->place_id,
             'name'        => $request->name,
             'description' => $request->description,
             'price'       =>$request->price,
             'images'      => json_encode($imagePaths),

        ]);

        return response()->json([
            'message' => 'Menu item created successfully',
            'menu'    => $menu,
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        \Log::warning('Menu item validation failed', ['errors' => $e->errors()]);
        return response()->json([
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        \Log::error('Error creating menu item: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'message' => 'An error occurred while creating the menu item',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    public function show($id)
    {
        $menu = Menu::findOrFail($id);
        return response()->json($menu);
    }

    public function update(Request $request, Menu $menu)
    {

        $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price'       => 'sometimes|required|numeric|min:0',
            'images'      => 'nullable|array|max:5',
            'images.*'    => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'price']);

        if ($request->hasFile('images')) {
            // Delete old images
            if ($menu->images) {
                foreach ($menu->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $imagePaths = [];
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('menus', 'public');
            }

            $data['images'] = $imagePaths;
        }

        $menu->update($data);

        return response()->json([
            'message' => 'Menu item updated successfully',
            'menu'    => $menu,
        ]);
    }

    public function destroy(Menu $menu)
    {
         
        if ($menu->images) {
            foreach ($menu->images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $menu->delete();

        return response()->json(['message' => 'Menu item deleted successfully']);
    }
}
