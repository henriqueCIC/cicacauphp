@extends('layouts.admin')

@section('page-title', 'Nova Legislação')

@section('content')
<div class="max-w-3xl bg-white rounded-lg shadow p-4 sm:p-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">Nova legislação</h1>
    <form action="{{ route('admin.legislations.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf
        <div>
            <label for="title" class="block font-medium mb-1">Título</label>
            <input id="title" name="title" type="text" value="{{ old('title') }}" required maxlength="255" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            @error('title') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="description" class="block font-medium mb-1">Descrição (até 200 caracteres)</label>
            <textarea id="description" name="description" rows="4" required maxlength="200" class="w-full border border-gray-300 rounded-lg px-3 py-2">{{ old('description') }}</textarea>
            @error('description') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="original_publication_date" class="block font-medium mb-1">Data original da publicação</label>
            <input id="original_publication_date" name="original_publication_date" type="date" value="{{ old('original_publication_date') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            @error('original_publication_date') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="source" class="block font-medium mb-1">Fonte</label>
            <input id="source" name="source" type="text" value="{{ old('source') }}" required maxlength="255" placeholder="Ex.: Diário Oficial da União" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            @error('source') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="file" class="block font-medium mb-1">PDF (até 10 MB)</label>
            <input id="file" name="file" type="file" accept="application/pdf,.pdf" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
            @error('file') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="external_link" class="block font-medium mb-1">Link externo</label>
            <input id="external_link" name="external_link" type="url" value="{{ old('external_link') }}" required maxlength="500" placeholder="https://" class="w-full border border-gray-300 rounded-lg px-3 py-2">
            @error('external_link') <p class="text-red-700 text-sm">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center gap-2">
            <input type="checkbox" name="publish_now" value="1" {{ old('publish_now') ? 'checked' : '' }}>
            Publicar agora no site
        </label>
        <div class="flex gap-4">
            <button type="submit" class="bg-primary text-white px-5 py-2 rounded-lg font-semibold">Salvar</button>
            <a href="{{ route('admin.legislations.index') }}" class="px-5 py-2 text-gray-700">Cancelar</a>
        </div>
    </form>
</div>
@endsection
