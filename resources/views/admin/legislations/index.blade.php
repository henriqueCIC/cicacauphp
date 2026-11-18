@extends('layouts.admin')

@section('page-title', 'Legislação')

@section('content')
<div class="flex items-center justify-between gap-4 mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Legislação</h1>
    <a href="{{ route('admin.legislations.create') }}" class="bg-primary text-white px-4 py-2 rounded-lg font-semibold">Nova legislação</a>
</div>

@if(session('success'))
    <p class="mb-6 p-4 bg-green-50 text-green-800 rounded-lg" role="status">{{ session('success') }}</p>
@endif

<div class="bg-white rounded-lg shadow divide-y divide-gray-200">
    @forelse($legislations as $legislation)
        <div class="p-4 sm:p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="font-semibold text-gray-900">{{ $legislation->title }}</h2>
                <p class="text-sm text-gray-600">{{ $legislation->source }} · {{ $legislation->original_publication_date?->format('d/m/Y') ?? 'Data não informada' }}</p>
                <p class="text-sm text-gray-600">Publicado por {{ $legislation->user->name }} · {{ $legislation->published_at ? 'Visível no site' : 'Rascunho' }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-4">
                <a href="{{ route('admin.legislations.edit', $legislation) }}" class="text-primary hover:underline">Editar</a>
                <form action="{{ route('admin.legislations.toggle-publish', $legislation) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="text-gray-700 hover:underline">{{ $legislation->published_at ? 'Despublicar' : 'Publicar' }}</button>
                </form>
                <form action="{{ route('admin.legislations.destroy', $legislation) }}" method="POST" onsubmit="return confirm('Excluir esta legislação e seu PDF?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-red-700 hover:underline">Excluir</button>
                </form>
            </div>
        </div>
    @empty
        <p class="p-6 text-gray-600">Nenhuma legislação cadastrada.</p>
    @endforelse
</div>

<div class="mt-6">{{ $legislations->links() }}</div>
@endsection
