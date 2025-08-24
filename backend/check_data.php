<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Apartment;
use App\Models\Notification;
use App\Models\Event;
use App\Models\Vote;

echo "=== KIỂM TRA DỮ LIỆU MẪU ===" . PHP_EOL . PHP_EOL;

// Kiểm tra Users
echo "USERS (" . User::count() . " người):" . PHP_EOL;
$users = User::select('name', 'email', 'role')->get();
foreach($users as $user) {
    echo "- {$user->name} ({$user->email}) - {$user->role}" . PHP_EOL;
}
echo PHP_EOL;

// Kiểm tra Apartments
echo "APARTMENTS (" . Apartment::count() . " căn hộ):" . PHP_EOL;
$apartments = Apartment::select('apartment_number', 'block', 'floor', 'area')->take(5)->get();
foreach($apartments as $apt) {
    echo "- {$apt->apartment_number} (Block {$apt->block}, Floor {$apt->floor}) - {$apt->area}m²" . PHP_EOL;
}
echo "..." . PHP_EOL . PHP_EOL;

// Kiểm tra Events
echo "EVENTS (" . Event::count() . " sự kiện):" . PHP_EOL;
$events = Event::select('title', 'type', 'start_time')->take(3)->get();
foreach($events as $event) {
    echo "- {$event->title} ({$event->type}) - {$event->start_time}" . PHP_EOL;
}
echo "..." . PHP_EOL . PHP_EOL;

// Kiểm tra Votes
echo "VOTES (" . Vote::count() . " cuộc bình chọn):" . PHP_EOL;
$votes = Vote::with('options')->get();
foreach($votes as $vote) {
    echo "- {$vote->title} ({$vote->status}) - {$vote->options->count()} options" . PHP_EOL;
}
echo PHP_EOL;

echo "=== DỮ LIỆU MẪU ĐÃ ĐƯỢC TẠO THÀNH CÔNG! ===" . PHP_EOL;
