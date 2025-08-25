<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Vote;

return new class extends Migration
{
    public function up(): void
    {
        // Create sample votes for testing
        Vote::create([
            'title' => 'Họp thường niên 2025',
            'description' => 'Cuộc họp thường niên để thảo luận về ngân sách và các vấn đề chung của chung cư.',
            'type' => 'general_meeting',
            'scope' => 'all',
            'start_time' => now(),
            'end_time' => now()->addDays(7),
            'status' => 'active',
            'created_by' => 1,
            'require_quorum' => true,
            'quorum_percentage' => 60,
        ]);

        Vote::create([
            'title' => 'Nâng cấp hệ thống thang máy',
            'description' => 'Đề xuất nâng cấp hệ thống thang máy để đảm bảo an toàn và hiệu quả vận hành.',
            'type' => 'facility_upgrade',
            'scope' => 'all',
            'start_time' => now(),
            'end_time' => now()->addDays(10),
            'status' => 'active',
            'created_by' => 1,
            'require_quorum' => true,
            'quorum_percentage' => 75,
        ]);

        Vote::create([
            'title' => 'Thay đổi quy định về thú cưng',
            'description' => 'Đề xuất thay đổi quy định cho phép nuôi thú cưng trong chung cư với các điều kiện nhất định.',
            'type' => 'rule_change',
            'scope' => 'all',
            'start_time' => now()->subDays(1),
            'end_time' => now()->addDays(5),
            'status' => 'active',
            'created_by' => 1,
            'require_quorum' => true,
            'quorum_percentage' => 50,
        ]);
    }

    public function down(): void
    {
        Vote::where('title', 'like', '%test%')->delete();
        Vote::where('title', 'Họp thường niên 2025')->delete();
        Vote::where('title', 'Nâng cấp hệ thống thang máy')->delete();
        Vote::where('title', 'Thay đổi quy định về thú cưng')->delete();
    }
};
