<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->replaceClassrooms([
            'VII A' => '7A',
            'VII B' => '7B',
            'VII C' => '7C',
            'VIII A' => '8A',
            'VIII B' => '8B',
            'VIII C' => '8C',
            'IX A' => '9A',
            'IX B' => '9B',
            'IX C' => '9C',
        ]);
    }

    public function down(): void
    {
        $this->replaceClassrooms([
            '7A' => 'VII A',
            '7B' => 'VII B',
            '7C' => 'VII C',
            '8A' => 'VIII A',
            '8B' => 'VIII B',
            '8C' => 'VIII C',
            '9A' => 'IX A',
            '9B' => 'IX B',
            '9C' => 'IX C',
        ]);
    }

    private function replaceClassrooms(array $classrooms): void
    {
        foreach ($classrooms as $from => $to) {
            DB::table('students')->where('classroom', $from)->update(['classroom' => $to]);
        }
    }
};
