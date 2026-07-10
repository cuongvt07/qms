<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;

/**
 * Service để convert file .doc sang .docx
 */
class DocConverterService
{
    /**
     * Convert file .doc sang .docx
     * 
     * @param string $docPath Đường dẫn tuyệt đối đến file .doc
     * @return string Đường dẫn tuyệt đối đến file .docx đã convert
     * @throws \Exception
     */
    public function convertDocToDocx(string $docPath): string
    {
        // Kiểm tra file tồn tại
        if (!file_exists($docPath)) {
            throw new \Exception("File không tồn tại: {$docPath}");
        }

        // Tạo tên file output
        $docxPath = preg_replace('/\.doc$/i', '.docx', $docPath);
        
        // Nếu đã là .docx thì return luôn
        if ($docPath === $docxPath) {
            return $docPath;
        }

        // Thử convert bằng các phương pháp khác nhau
        try {
            // Method 1: Dùng LibreOffice (nếu có cài)
            if ($this->convertWithLibreOffice($docPath, $docxPath)) {
                return $docxPath;
            }

            // Method 2: Dùng unoconv (nếu có cài)
            if ($this->convertWithUnoconv($docPath, $docxPath)) {
                return $docxPath;
            }

            // Method 3: Dùng COM (Windows only - Microsoft Word)
            if ($this->convertWithCOM($docPath, $docxPath)) {
                return $docxPath;
            }

            throw new \Exception('Không tìm thấy công cụ convert. Vui lòng cài LibreOffice hoặc Microsoft Word.');

        } catch (\Throwable $e) {
            throw new \Exception("Lỗi convert .doc sang .docx: " . $e->getMessage());
        }
    }

    /**
     * Convert bằng LibreOffice
     */
    private function convertWithLibreOffice(string $docPath, string $docxPath): bool
    {
        // Tìm LibreOffice
        $libreOfficePaths = [
            'C:\Program Files\LibreOffice\program\soffice.exe',
            'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
            'soffice', // Nếu có trong PATH
        ];

        $soffice = null;
        foreach ($libreOfficePaths as $path) {
            if (file_exists($path) || $path === 'soffice') {
                $soffice = $path;
                break;
            }
        }

        if (!$soffice) {
            return false;
        }

        $outputDir = dirname($docPath);
        
        try {
            $result = Process::run([
                $soffice,
                '--headless',
                '--convert-to',
                'docx',
                '--outdir',
                $outputDir,
                $docPath
            ]);

            return $result->successful() && file_exists($docxPath);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Convert bằng unoconv
     */
    private function convertWithUnoconv(string $docPath, string $docxPath): bool
    {
        try {
            $outputDir = dirname($docPath);
            
            $result = Process::run([
                'unoconv',
                '-f',
                'docx',
                '-o',
                $docxPath,
                $docPath
            ]);

            return $result->successful() && file_exists($docxPath);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Convert bằng COM (Microsoft Word trên Windows)
     */
    private function convertWithCOM(string $docPath, string $docxPath): bool
    {
        // Chỉ hoạt động trên Windows với COM extension
        if (!extension_loaded('com_dotnet')) {
            return false;
        }

        try {
            // Format constants
            $wdFormatDocumentDefault = 16; // .docx format
            
            // Khởi tạo Word application
            $word = new \COM("Word.Application");
            $word->Visible = false;
            $word->DisplayAlerts = false;

            // Mở file .doc
            $doc = $word->Documents->Open($docPath);

            // Save as .docx
            $doc->SaveAs($docxPath, $wdFormatDocumentDefault);
            $doc->Close();

            // Đóng Word
            $word->Quit();

            // Giải phóng COM object
            unset($doc);
            unset($word);

            return file_exists($docxPath);

        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Kiểm tra file có phải .doc không
     */
    public function isDocFile(string $path): bool
    {
        return preg_match('/\.doc$/i', $path) === 1;
    }

    /**
     * Kiểm tra các công cụ convert có sẵn không
     */
    public function getAvailableConverter(): ?string
    {
        // Check LibreOffice
        $libreOfficePaths = [
            'C:\Program Files\LibreOffice\program\soffice.exe',
            'C:\Program Files (x86)\LibreOffice\program\soffice.exe',
        ];

        foreach ($libreOfficePaths as $path) {
            if (file_exists($path)) {
                return 'LibreOffice';
            }
        }

        // Check unoconv
        try {
            $result = Process::run(['unoconv', '--version']);
            if ($result->successful()) {
                return 'unoconv';
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Check COM/Word
        if (extension_loaded('com_dotnet')) {
            try {
                $word = new \COM("Word.Application");
                $word->Quit();
                unset($word);
                return 'Microsoft Word';
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return null;
    }
}
