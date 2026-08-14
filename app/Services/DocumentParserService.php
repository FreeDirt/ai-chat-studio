<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser as PdfParser;

class DocumentParserService
{
    /**
     * Extract raw text from an uploaded file.
     */
    public function extractText(UploadedFile $file): string
    {
        $ext  = strtolower($file->getClientOriginalExtension());
        $mime = $file->getMimeType() ?? '';
        $path = $file->getRealPath();

        return match (true) {
            $ext === 'pdf' || str_contains($mime, 'pdf')               => $this->parsePdf($path),
            in_array($ext, ['docx']) || str_contains($mime, 'word')    => $this->parseDocx($path),
            in_array($ext, ['txt', 'md', 'php', 'js', 'py', 'ts',
                             'java', 'go', 'rb', 'rs', 'cs', 'cpp',
                             'c', 'h', 'html', 'css', 'json', 'yaml',
                             'yml', 'xml', 'sh', 'sql', 'env'])        => $this->parsePlainText($path),
            $ext === 'csv'                                              => $this->parseCsv($path),
            default                                                     => $this->parsePlainText($path),
        };
    }

    /**
     * Split text into overlapping chunks for embedding.
     * Uses paragraph-aware splitting: tries to break on newlines first,
     * then on sentence boundaries, falling back to hard character splits.
     */
    public function chunkText(string $text, int $chunkSize = 1000, int $overlap = 200): array
    {
        $text   = $this->normaliseWhitespace($text);
        $chunks = [];
        $length = mb_strlen($text);

        if ($length <= $chunkSize) {
            return [$text];
        }

        // Split into paragraphs first
        $paragraphs = preg_split('/\n{2,}/', $text);
        $buffer     = '';

        foreach ($paragraphs as $para) {
            $para = trim($para);
            if (empty($para)) continue;

            // If adding this paragraph exceeds chunk size, flush current buffer
            if (mb_strlen($buffer) + mb_strlen($para) + 2 > $chunkSize && $buffer !== '') {
                $chunks[] = trim($buffer);
                // Keep overlap
                $buffer = mb_substr($buffer, max(0, mb_strlen($buffer) - $overlap));
            }

            $buffer .= ($buffer ? "\n\n" : '') . $para;

            // If a single paragraph is too long, hard-split it
            while (mb_strlen($buffer) > $chunkSize) {
                $chunks[] = mb_substr($buffer, 0, $chunkSize);
                $buffer   = mb_substr($buffer, $chunkSize - $overlap);
            }
        }

        if (trim($buffer) !== '') {
            $chunks[] = trim($buffer);
        }

        return array_values(array_filter($chunks, fn($c) => mb_strlen(trim($c)) > 20));
    }

    // ===== PARSERS =====

    private function parsePdf(string $path): string
    {
        try {
            $parser = new PdfParser();
            $pdf    = $parser->parseFile($path);
            $text   = $pdf->getText();
            return $text ?: throw new \RuntimeException('PDF appears to be empty or image-only.');
        } catch (\Throwable $e) {
            Log::warning("PDF parse failed: {$e->getMessage()}");
            throw new \RuntimeException("Could not extract text from PDF: {$e->getMessage()}");
        }
    }

    private function parseDocx(string $path): string
    {
        try {
            $phpWord  = \PhpOffice\PhpWord\IOFactory::load($path);
            $sections = $phpWord->getSections();
            $text     = '';

            foreach ($sections as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    } elseif ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                        foreach ($element->getElements() as $child) {
                            if (method_exists($child, 'getText')) {
                                $text .= $child->getText();
                            }
                        }
                        $text .= "\n";
                    } elseif ($element instanceof \PhpOffice\PhpWord\Element\Table) {
                        foreach ($element->getRows() as $row) {
                            $cells = [];
                            foreach ($row->getCells() as $cell) {
                                $cellText = '';
                                foreach ($cell->getElements() as $cel) {
                                    if (method_exists($cel, 'getText')) {
                                        $cellText .= $cel->getText() . ' ';
                                    }
                                }
                                $cells[] = trim($cellText);
                            }
                            $text .= implode(' | ', $cells) . "\n";
                        }
                        $text .= "\n";
                    }
                }
            }

            return $text;
        } catch (\Throwable $e) {
            Log::warning("DOCX parse failed: {$e->getMessage()}");
            throw new \RuntimeException("Could not read DOCX: {$e->getMessage()}");
        }
    }

    private function parsePlainText(string $path): string
    {
        $content = file_get_contents($path);
        if ($content === false) throw new \RuntimeException('Could not read file.');
        return $content;
    }

    private function parseCsv(string $path): string
    {
        $lines = [];
        if (($handle = fopen($path, 'r')) !== false) {
            $headers = null;
            while (($row = fgetcsv($handle)) !== false) {
                if ($headers === null) {
                    $headers = $row;
                } else {
                    $pairs = [];
                    foreach ($headers as $i => $h) {
                        $pairs[] = trim($h) . ': ' . trim($row[$i] ?? '');
                    }
                    $lines[] = implode(', ', $pairs);
                }
            }
            fclose($handle);
        }
        return implode("\n", $lines);
    }

    private function normaliseWhitespace(string $text): string
    {
        // Replace tabs with spaces, collapse 3+ newlines to 2
        $text = str_replace("\t", '    ', $text);
        $text = preg_replace('/\r\n|\r/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        return trim($text);
    }
}
