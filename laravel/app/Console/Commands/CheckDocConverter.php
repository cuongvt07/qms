<?php

namespace App\Console\Commands;

use App\Services\DocConverterService;
use Illuminate\Console\Command;

class CheckDocConverter extends Command
{
    protected $signature = 'doc:check-converter';
    protected $description = 'Kiểm tra công cụ convert .doc sang .docx có sẵn không';

    public function handle(DocConverterService $converter): int
    {
        $this->info('🔍 Đang kiểm tra các công cụ convert .doc sang .docx...');
        $this->newLine();

        $available = $converter->getAvailableConverter();

        if ($available) {
            $this->info("✅ Tìm thấy: {$available}");
            $this->info('Hệ thống có thể tự động convert file .doc sang .docx');
        } else {
            $this->warn('❌ Không tìm thấy công cụ convert nào!');
            $this->newLine();
            $this->info('📋 Các công cụ hỗ trợ:');
            $this->line('  1. LibreOffice (khuyến nghị) - Download: https://www.libreoffice.org/download/');
            $this->line('  2. Microsoft Word (cần enable COM extension trong php.ini)');
            $this->line('  3. unoconv (cần LibreOffice làm backend)');
            $this->newLine();
            $this->warn('⚠️  Nếu không cài công cụ convert, user phải tự convert .doc sang .docx trước khi upload.');
        }

        $this->newLine();
        
        // Kiểm tra COM extension
        if (extension_loaded('com_dotnet')) {
            $this->info('✅ COM extension đã được enable (có thể dùng Microsoft Word)');
        } else {
            $this->line('ℹ️  COM extension chưa enable (không thể dùng Microsoft Word để convert)');
            $this->line('   Để enable: Mở php.ini, bỏ comment dòng: extension=com_dotnet');
        }

        return self::SUCCESS;
    }
}
