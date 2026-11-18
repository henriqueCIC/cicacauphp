@extends('layouts.admin')

@section('page-title', 'Editar Legislação')

@section('content')
<div class="max-w-3xl bg-white rounded-lg shadow p-4 sm:p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Editar legislação</h1>
    <form action="{{ route('admin.legislations.update', $legislation) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        @method('PUT')
        <div>
            <label for="title" class="block font-medium mb-1">Título</label>
            <input id="title" name="title" type="text" value="{{ old('title', $legislation->title) }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            @error('title') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="description" class="block font-medium mb-1">Descrição (até 200 caracteres)</label>
            <textarea id="description" name="description" rows="4" required maxlength="200" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('description', $legislation->description) }}</textarea>
            @error('description') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="original_publication_date" class="block font-medium mb-1">Data original da publicação</label>
            <input id="original_publication_date" name="original_publication_date" type="date" value="{{ old('original_publication_date', $legislation->original_publication_date?->format('Y-m-d')) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            @error('original_publication_date') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="source" class="block font-medium mb-1">Fonte</label>
            <input id="source" name="source" type="text" value="{{ old('source', $legislation->source) }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            @error('source') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="file" class="block font-medium mb-1">Substituir PDF (opcional, até 10 MB)</label>
            <input id="file" name="file" type="file" accept="application/pdf,.pdf" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            <p class="mt-1 text-sm text-gray-600">Deixe vazio para manter o PDF atual.</p>
            @error('file') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="external_link" class="block font-medium mb-1">Link externo</label>
            <input id="external_link" name="external_link" type="url" value="{{ old('external_link', $legislation->external_link) }}" required maxlength="500" placeholder="https://" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            @error('external_link') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div class="flex gap-4">
            <button type="submit" class="bg-primary text-white px-5 py-2 rounded-lg font-semibold">Salvar alterações</button>
            <a href="{{ route('admin.legislations.index') }}" class="px-5 py-2 text-gray-700">Cancelar</a>
        </div>
    </form>
</div>
@endsection
