<div class="flex gap-2">
    <a href="{{ route('teachers.edit', $teacher) }}" class="rounded-lg bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Edit</a>
    <form method="post" action="{{ route('teachers.destroy', $teacher) }}" onsubmit="return confirm('Hapus data guru?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Hapus</button></form>
</div>
