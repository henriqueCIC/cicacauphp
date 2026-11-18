<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Legislation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LegislationController extends Controller
{
    public function index()
    {
        $legislations = Legislation::with('user')->latest()->paginate(15);

        return view('admin.legislations.index', compact('legislations'));
    }

    public function create()
    {
        return view('admin.legislations.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:200',
            'original_publication_date' => 'required|date',
            'source' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf|max:10240',
            'external_link' => 'required|url|max:500',
        ]);

        $validated['file'] = $request->file('file')->store('legislations', 'public');
        $validated['user_id'] = $request->user()->id;
        $validated['published_at'] = $request->boolean('publish_now') ? now() : null;

        Legislation::create($validated);

        return redirect()->route('admin.legislations.index')->with('success', 'Legislação cadastrada com sucesso.');
    }

    public function edit(Legislation $legislation)
    {
        return view('admin.legislations.edit', compact('legislation'));
    }

    public function update(Request $request, Legislation $legislation)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:200',
            'original_publication_date' => 'required|date',
            'source' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf|max:10240',
            'external_link' => 'required|url|max:500',
        ]);

        $oldFile = $legislation->file;

        if ($request->hasFile('file')) {
            $validated['file'] = $request->file('file')->store('legislations', 'public');
        } else {
            unset($validated['file']);
        }

        $legislation->update($validated);

        if (isset($validated['file'])) {
            Storage::disk('public')->delete($oldFile);
        }

        return redirect()->route('admin.legislations.index')->with('success', 'Legislação atualizada com sucesso.');
    }

    public function togglePublish(Legislation $legislation)
    {
        $legislation->update([
            'published_at' => $legislation->published_at ? null : now(),
        ]);

        return redirect()->route('admin.legislations.index')->with('success', 'Status da legislação atualizado.');
    }

    public function destroy(Legislation $legislation)
    {
        $file = $legislation->file;
        $legislation->delete();
        Storage::disk('public')->delete($file);

        return redirect()->route('admin.legislations.index')->with('success', 'Legislação excluída.');
    }
}
