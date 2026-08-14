<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;

class PersonaController extends Controller
{
    public function index()
    {
        $personas = Persona::orderBy('sort_order')->get();
        return view('personas.index', compact('personas'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'icon'          => 'nullable|string|max:2048',
            'system_prompt' => 'required|string|max:65536',
            'color'         => 'nullable|string|max:20',
            'is_active'     => 'boolean',
        ]);

        $data['sort_order'] = Persona::max('sort_order') + 1;
        $data['icon']       = $data['icon'] ?? '🤖';
        $data['color']      = $data['color'] ?? '#6c63ff';
        $data['is_active']  = $request->boolean('is_active', true);

        $persona = Persona::create($data);

        return response()->json(['success' => true, 'persona' => $persona]);
    }

    public function update(Request $request, Persona $persona)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'icon'          => 'nullable|string|max:2048',
            'system_prompt' => 'required|string|max:65536',
            'color'         => 'nullable|string|max:20',
            'is_active'     => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $persona->update($data);

        return response()->json(['success' => true, 'persona' => $persona->fresh()]);
    }

    public function destroy(Persona $persona)
    {
        $persona->delete();
        return response()->json(['success' => true]);
    }

    public function reorder(Request $request)
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:personas,id']);

        foreach ($request->order as $index => $id) {
            Persona::where('id', $id)->update(['sort_order' => $index + 1]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Upload an icon image / GIF for a persona.
     */
    public function uploadIcon(Request $request)
    {
        $request->validate([
            'icon_file' => 'required|file|mimes:gif,png,jpg,jpeg,svg,webp|max:5120',
        ]);

        $file = $request->file('icon_file');
        $filename = \Illuminate\Support\Str::uuid() . '.' . strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('persona_icons', $filename, 'public');

        return response()->json([
            'success' => true,
            'url'     => '/storage/' . $path,
        ]);
    }
}
