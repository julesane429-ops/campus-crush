@extends('layouts.app')

@section('content')
<div class="min-h-screen gradient-bg flex justify-center">
    <div class="w-full max-w-md flex flex-col text-white px-6 py-8">
        <a href="{{ route('settings') }}" class="flex items-center gap-2 mb-6 text-white/70 hover:text-white">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Retour
        </a>
        <h1 class="text-2xl font-bold mb-4">Notifications</h1>
        <p class="text-white/50">Les notifications seront disponibles prochainement.</p>
    </div>
</div>
@endsection
