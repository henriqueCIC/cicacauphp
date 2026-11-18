@extends('layouts.app')

@section('meta_title', 'Legislação | CI Cacau')
@section('meta_description', 'Legislação relacionada à cadeia produtiva do cacau.')

@section('content')
<section class="bg-gradient-to-br from-primary to-secondary text-white py-16 md:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl md:text-5xl font-bold mb-4">Legislação</h1>
        <p class="text-xl md:text-2xl text-white/90">Legislação relacionada à cadeia produtiva do cacau</p>
    </div>
</section>

<section class="py-12 md:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($legislations->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8">
                @foreach($legislations as $legislation)
                    <article class="min-w-0 break-words bg-white rounded-xl border border-gray-200 shadow-lg p-6 flex flex-col">
                        <p class="text-sm text-gray-600 mb-3">Publicado em {{ $legislation->published_at->format('d/m/Y') }}</p>
                        @if($legislation->original_publication_date)
                            <p class="text-sm text-gray-600 mb-1">Data da legislação: {{ $legislation->original_publication_date->format('d/m/Y') }}</p>
                        @endif
                        @if($legislation->source)
                            <p class="text-sm text-gray-600 mb-3">Fonte: {{ $legislation->source }}</p>
                        @endif
                        <h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-3">{{ $legislation->title }}</h2>
                        <p class="text-gray-600 mb-6 flex-1 line-clamp-4">{{ Str::limit($legislation->description, 200) }}</p>
                        <p class="text-sm text-gray-600 mb-4">Publicado por {{ $legislation->user->name }}</p>
                        <div class="flex flex-wrap gap-4 border-t border-gray-200 pt-4">
                            <a href="{{ asset('storage/' . $legislation->file) }}" target="_blank" rel="noopener noreferrer" class="text-primary font-semibold hover:underline">Abrir PDF</a>
                            <a href="{{ $legislation->external_link }}" target="_blank" rel="noopener noreferrer" class="text-primary font-semibold hover:underline">Acessar link</a>
                        </div>
                    </article>
                @endforeach
            </div>

            @if($legislations->hasPages())
                <div class="mt-12">{{ $legislations->links() }}</div>
            @endif
        @else
            <p class="text-center text-gray-600 py-16">Em breve, documentos e links de legislação estarão disponíveis aqui.</p>
        @endif
    </div>
</section>
@endsection
