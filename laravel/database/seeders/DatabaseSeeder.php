<?php

namespace Database\Seeders;

use App\Models\DocumentCategory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user
        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@qms.local',
            'password' => Hash::make('password'),
            'is_admin' => true,
        ]);

        // User nhập liệu thường
        User::create([
            'name'     => 'Nguyễn Văn A',
            'email'    => 'user@qms.local',
            'password' => Hash::make('password'),
            'is_admin' => false,
        ]);

        // Mục tài liệu mẫu
        $categories = [
            ['ten_muc' => 'QTQL - Quy trình quản lý chất lượng', 'mo_ta' => 'Các biểu mẫu thuộc quy trình QTQL'],
            ['ten_muc' => 'QTMB - Quy trình mua bán',            'mo_ta' => 'Các biểu mẫu thuộc quy trình mua bán'],
            ['ten_muc' => 'QTSX - Quy trình sản xuất',           'mo_ta' => 'Biểu mẫu kiểm soát sản xuất'],
        ];
        foreach ($categories as $cat) {
            DocumentCategory::create($cat);
        }
    }
}
