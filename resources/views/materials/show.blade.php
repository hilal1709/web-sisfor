<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $material->title }} - Academy Bridge</title>
<script src="https://cdn.tailwindcss.com/3.4.16"></script>
<script>tailwind.config={theme:{extend:{colors:{primary:'#4F9DA6',secondary:'#F5B041'},borderRadius:{'none':'0px','sm':'4px',DEFAULT:'8px','md':'12px','lg':'16px','xl':'20px','2xl':'24px','3xl':'32px','full':'9999px','button':'8px'}}}}</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Pacifico&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
<style>
:where([class^="ri-"])::before { content: "\\f3c2"; }
body { font-family: 'Inter', sans-serif; background-color: #f9fafb; }
</style>
</head>
<body>
<!-- Header & Navigation -->
<header class="bg-white shadow-sm sticky top-0 z-50">
<div class="container mx-auto px-4 py-3 flex items-center justify-between">
<div class="flex items-center gap-2">
<div class="w-10 h-10 flex items-center justify-center bg-primary rounded-lg text-white">
<i class="ri-book-open-line ri-lg"></i>
</div>
<a href="{{ route('dashboard') }}" class="text-2xl font-['Pacifico'] text-primary">Academy Bridge</a>
</div>
<nav class="hidden md:flex items-center space-x-6">
<a href="{{ route('dashboard') }}" class="font-medium text-gray-600 hover:text-gray-900">Beranda</a>
<a href="{{ route('materials.index') }}" class="font-medium text-gray-900 border-b-2 border-primary pb-1">Materi</a>
<a href="{{ route('discussions.my') }}" class="font-medium text-gray-600 hover:text-gray-900">Forum</a>
<a href="{{ route('notifications.index') }}" class="font-medium text-gray-600 hover:text-gray-900">Notifikasi</a>
<a href="{{ route('analytics.index') }}" class="font-medium text-gray-600 hover:text-gray-900">Analitik</a>
<a href="{{ route('profile.edit') }}" class="font-medium text-gray-600 hover:text-gray-900">Profil</a>
</nav>
<div class="flex items-center gap-3">
<button class="hidden md:flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200">
<i class="ri-notification-3-line ri-lg text-gray-700"></i>
</button>
@include('components.profile-dropdown')
</div>
</div>
</header>

<main>
<section class="py-12 bg-white min-h-screen">
<div class="container mx-auto px-4">
<div class="max-w-3xl mx-auto">
<div class="bg-white rounded-xl shadow-md border border-gray-100 p-8">
<div class="flex items-center justify-between mb-4">
<h1 class="text-2xl md:text-3xl font-bold text-gray-900 flex items-center gap-2">
<i class="ri-file-text-line text-primary"></i>
{{ $material->title }}
</h1>
<div class="flex items-center gap-2">
@if($material->is_verified)
<span class="bg-green-100 text-green-800 text-xs font-semibold px-3 py-1 rounded-full flex items-center gap-1">
<i class="ri-verified-badge-line"></i> Terverifikasi Dosen
</span>
@endif
@auth
@if(auth()->id() === $material->user_id)
<a href="{{ route('materials.edit', $material) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-button font-medium flex items-center gap-2 hover:bg-yellow-600">
<i class="ri-edit-2-line"></i> Edit
</a>
@endif
@endauth
</div>
</div>
<div class="flex flex-wrap gap-4 text-sm text-gray-600 mb-6">
<span class="flex items-center gap-1"><i class="ri-user-3-line"></i> {{ $material->user->name }}</span>
<span class="flex items-center gap-1"><i class="ri-calendar-line"></i> {{ $material->created_at->format('d M Y') }}</span>
<span class="flex items-center gap-1"><i class="ri-book-2-line"></i> {{ $material->course->name ?? '-' }}</span>
<span class="flex items-center gap-1"><i class="ri-download-2-line"></i> {{ $material->downloads_count }} unduhan</span>
</div>
<div class="mb-8">
<h2 class="text-lg font-semibold text-gray-900 mb-2">Deskripsi Materi</h2>
<p class="text-gray-700 leading-relaxed">{{ $material->description }}</p>
</div>
<div class="mb-8">
<h2 class="text-lg font-semibold text-gray-900 mb-2">File Materi</h2>
<div class="bg-gray-50 rounded-lg p-4 flex items-center justify-between">
<div class="flex items-center gap-3">
<div class="w-12 h-12 flex items-center justify-center bg-primary/10 rounded-lg">
<i class="ri-file-download-line text-2xl text-primary"></i>
</div>
<div>
<p class="text-sm font-medium text-gray-900">{{ $material->original_filename ?? basename($material->file_path) }}</p>
<p class="text-xs text-gray-500">{{ strtoupper(pathinfo($material->file_path, PATHINFO_EXTENSION)) }}
@if(file_exists(storage_path('app/public/' . $material->file_path)))
• {{ number_format(filesize(storage_path('app/public/' . $material->file_path)) / 1024 / 1024, 2) }} MB
@endif
</p>
</div>
</div>
<a href="{{ route('materials.download', $material) }}" class="bg-primary text-white px-5 py-2 rounded-button font-medium flex items-center gap-2 hover:bg-primary/90">
<i class="ri-download-2-line"></i> Download
</a>
</div>
</div>

<!-- Kategori Materi -->
<div class="mb-8">
<h2 class="text-lg font-semibold text-gray-900 mb-2">Kategori Materi</h2>
<div class="flex flex-wrap gap-3 text-sm">
@if($material->fakultas)<span class="bg-gray-100 px-3 py-1 rounded-full">Fakultas: {{ $material->fakultas }}</span>@endif
@if($material->jurusan)<span class="bg-gray-100 px-3 py-1 rounded-full">Jurusan: {{ $material->jurusan }}</span>@endif
@if($material->semester)<span class="bg-gray-100 px-3 py-1 rounded-full">Semester: {{ $material->semester }}</span>@endif
@if($material->mata_kuliah)<span class="bg-gray-100 px-3 py-1 rounded-full">Mata Kuliah: {{ $material->mata_kuliah }}</span>@endif
@if(!$material->fakultas && !$material->jurusan && !$material->semester && !$material->mata_kuliah)
<p class="text-gray-500 italic">Informasi kategori tidak tersedia untuk materi ini.</p>
@endif
</div>
</div>

<!-- Diskusi -->
<div class="border-t border-gray-200 pt-6">
<h2 class="text-lg font-semibold text-gray-900 mb-4">Diskusi Materi</h2>
@auth
<form action="{{ route('discussions.store') }}" method="POST" class="mb-6">
@csrf
<input type="hidden" name="material_id" value="{{ $material->id }}">
<div>
<textarea id="content" name="content" rows="3" class="shadow-sm block w-full focus:ring-primary focus:border-primary sm:text-sm border border-gray-300 rounded-md" placeholder="Tulis komentar atau pertanyaan..."></textarea>
</div>
<div class="mt-3 flex items-center justify-end">
<button type="submit" class="bg-primary text-white px-5 py-2 rounded-button font-medium flex items-center gap-2 hover:bg-primary/90">
<i class="ri-send-plane-2-line"></i> Kirim
</button>
</div>
</form>
@else
<div class="rounded-md bg-yellow-50 p-4 mb-6">
<div class="flex">
<div class="ml-3">
<p class="text-sm font-medium text-yellow-800">
Silakan <a href="{{ route('login') }}" class="font-bold underline">login</a> untuk ikut diskusi materi ini.
</p>
</div>
</div>
</div>
@endauth
<div class="space-y-6">
@forelse($material->discussions as $discussion)
<div class="bg-gray-50 rounded-lg p-6">
<div class="flex space-x-3">
<div class="flex-1">
<div class="flex items-center justify-between">
<h3 class="text-sm font-medium text-gray-900">{{ $discussion->user->name }}</h3>
<p class="text-sm text-gray-500">{{ $discussion->created_at->diffForHumans() }}</p>
</div>
<div class="mt-1 text-sm text-gray-700">
<p>{{ $discussion->content }}</p>
</div>
</div>
</div>
</div>
@empty
<p class="text-gray-500 text-center py-4">Belum ada diskusi.</p>
@endforelse
</div>
</div>
</div>
</div>
</div>
</section>
</main>

<!-- Footer -->
<footer class="bg-gray-900 text-white py-12">
<div class="container mx-auto px-4">
<div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
<div>
<div class="flex items-center gap-2 mb-4">
<div class="w-10 h-10 flex items-center justify-center bg-white rounded-lg">
<i class="ri-book-open-line ri-lg text-primary"></i>
</div>
<a href="{{ route('dashboard') }}" class="text-2xl font-['Pacifico'] text-white">Academy Bridge</a>
</div>
<p class="text-gray-400 mb-4">Platform berbagi materi kuliah yang memudahkan mahasiswa dan dosen untuk mengakses, berbagi, dan berkolaborasi dalam pembelajaran akademik.</p>
</div>
<div>
<h3 class="text-lg font-semibold mb-4">Navigasi</h3>
<ul class="space-y-2">
<li><a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white">Beranda</a></li>
<li><a href="{{ route('materials.index') }}" class="text-gray-400 hover:text-white">Materi</a></li>
<li><a href="{{ route('discussions.my') }}" class="text-gray-400 hover:text-white">Forum</a></li>
<li><a href="{{ route('profile.edit') }}" class="text-gray-400 hover:text-white">Profil</a></li>
</ul>
</div>
<div>
<h3 class="text-lg font-semibold mb-4">Fitur</h3>
<ul class="space-y-2">
<li><a href="{{ route('materials.saved') }}" class="text-gray-400 hover:text-white">Materi Tersimpan</a></li>
<li><a href="{{ route('materials.create') }}" class="text-gray-400 hover:text-white">Upload Materi</a></li>
<li><a href="{{ route('materials.index', ['verified' => 1]) }}" class="text-gray-400 hover:text-white">Materi Terverifikasi</a></li>
</ul>
</div>
<div>
<h3 class="text-lg font-semibold mb-4">Kontak</h3>
<ul class="space-y-2">
<li class="flex items-center gap-2">
<i class="ri-mail-line text-gray-400"></i>
<a href="mailto:info@academybridge.id" class="text-gray-400 hover:text-white">info@academybridge.id</a>
</li>
<li class="flex items-center gap-2">
<i class="ri-phone-line text-gray-400"></i>
<a href="tel:+6281234567890" class="text-gray-400 hover:text-white">+62 812 3456 7890</a>
</li>
</ul>
</div>
</div>
<div class="pt-8 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center">
<p class="text-gray-400 text-sm mb-4 md:mb-0">&copy; 2025 Academy Bridge. Hak Cipta Dilindungi.</p>
<div class="flex gap-4">
<a href="#" class="text-gray-400 hover:text-white text-sm">Syarat & Ketentuan</a>
<a href="#" class="text-gray-400 hover:text-white text-sm">Kebijakan Privasi</a>
</div>
</div>
</div>
</footer>
</body>
</html>