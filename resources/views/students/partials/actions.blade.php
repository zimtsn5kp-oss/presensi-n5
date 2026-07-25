<div class="flex gap-2">
    <a href="{{ route('students.edit', $student) }}" class="rounded-lg bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Edit</a>
    <form method="post" action="{{ route('students.destroy', $student) }}" onsubmit="return confirm('Hapus data murid?')">@csrf @method('DELETE')<button class="rounded-lg bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">Hapus</button></form>
</div>
