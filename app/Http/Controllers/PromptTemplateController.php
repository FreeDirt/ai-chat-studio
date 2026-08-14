<?php

namespace App\Http\Controllers;

use App\Models\PromptTemplate;
use Illuminate\Http\Request;

class PromptTemplateController extends Controller
{
    /**
     * List all prompt templates.
     */
    public function index()
    {
        $templates = PromptTemplate::orderBy('category')->orderBy('sort_order')->get();
        return response()->json(['templates' => $templates]);
    }

    /**
     * Store a new prompt template.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'    => 'required|string|max:100',
            'shortcut' => 'nullable|string|max:30',
            'content'  => 'required|string',
            'category' => 'nullable|string|max:50',
        ]);

        $data['category']   = $data['category'] ?? 'Custom';
        $data['sort_order'] = PromptTemplate::max('sort_order') + 1;

        $template = PromptTemplate::create($data);

        return response()->json(['success' => true, 'template' => $template]);
    }

    /**
     * Delete a prompt template.
     */
    public function destroy(PromptTemplate $promptTemplate)
    {
        $promptTemplate->delete();
        return response()->json(['success' => true]);
    }
}
