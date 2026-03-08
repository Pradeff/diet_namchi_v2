<?php

namespace App\Service;

use App\Entity\Vitinerary;
use App\Entity\Vcontact;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use TCPDF;

/**
 * Tour PDF generator for Lets go Sikkim Tours & Treks
 * Using official Endroid QR Code from github.com/endroid/qr-code
 */
class PackagePdf extends TCPDF
{
    private Vitinerary $vitinerary;
    private ?Vcontact $contact;
    private string $logoPath;
    private string $placeholderPath;
    private string $projectDir;
    private ?string $qrCodeImagePath = null;
    private string $packageUrl;

    public function __construct(
        Vitinerary $vitinerary,
        ?Vcontact $contact,
        string $logoPath = '',
        string $placeholderPath = '',
        string $packageUrl = ''
    ) {
        parent::__construct('P', 'mm', 'A4', true, 'UTF-8', false);

        $this->vitinerary = $vitinerary;
        $this->contact = $contact;
        $this->logoPath = $logoPath;
        $this->placeholderPath = $placeholderPath;
        $this->packageUrl = $packageUrl;
        $this->projectDir = dirname(__DIR__, 2);

        $this->setupDocument();

        if (!empty($this->packageUrl)) {
            $this->qrCodeImagePath = $this->generateQrCode();
        }
    }

    private function setupDocument(): void
    {
        $this->SetCreator('Ease Tours & Treks');
        $this->SetAuthor('Ease Tours & Treks');
        $this->SetTitle($this->vitinerary->getTitle() . ' - Tour Package');
        $this->SetMargins(0, 0, 0);
        $this->SetAutoPageBreak(true, 0);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->SetFont('helvetica', '', 9);
    }

    /**
     * Generate QR Code using official Endroid Builder pattern
     * Based on: https://github.com/endroid/qr-code
     */
    private function generateQrCode(): ?string
    {
        if (empty($this->packageUrl)) {
            error_log('[PackagePDF][QR] Package URL is empty');
            return null;
        }

        $cleanUrl = $this->cleanUrl($this->packageUrl);
        error_log('[PackagePDF][QR] Generating QR code for: ' . $cleanUrl);

        try {
            // Official Endroid Builder pattern from GitHub
            $builder = new Builder(
                writer: new PngWriter(),
                writerOptions: [],
                validateResult: false,
                data: $cleanUrl,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 300,
                margin: 10,
                roundBlockSizeMode: RoundBlockSizeMode::Margin,
            );

            $result = $builder->build();

            // Save to temporary file
            $tmpFile = sys_get_temp_dir() . '/qr_' . uniqid() . '_' . time() . '.png';
            $result->saveToFile($tmpFile);

            if (file_exists($tmpFile) && filesize($tmpFile) > 0) {
                error_log('[PackagePDF][QR] ✓ QR code generated successfully: ' . $tmpFile . ' (' . filesize($tmpFile) . ' bytes)');
                return $tmpFile;
            }

            error_log('[PackagePDF][QR] ✗ QR code file was not created');
            return null;

        } catch (\Throwable $e) {
            error_log('[PackagePDF][QR] ✗ Error generating QR code: ' . $e->getMessage());
            error_log('[PackagePDF][QR] Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }

    /**
     * Clean and validate URL
     */
    private function cleanUrl(string $url): string
    {
        // Remove 'public/' prefix if exists
        $cleanUrl = str_replace('public/', '', $url);
        $cleanUrl = trim($cleanUrl);

        // Ensure URL has protocol
        if (!preg_match('/^https?:\/\//', $cleanUrl)) {
            if (!empty($_SERVER['HTTP_HOST'])) {
                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $cleanUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/' . ltrim($cleanUrl, '/');
            }
        }

        return $cleanUrl;
    }

    /**
     * Draw QR Code in top right corner
     */
    private function drawQrcode(): void
    {
        // White background for QR code area
        $this->SetFillColor(255, 255, 255);
        $this->RoundedRect(172, 3, 35, 35, 3, '1111', 'F');

        if (!empty($this->qrCodeImagePath) && file_exists($this->qrCodeImagePath)) {
            try {
                error_log('[PackagePDF][QR] Drawing QR code from: ' . $this->qrCodeImagePath);

                // Add QR code image
                $this->Image(
                    $this->qrCodeImagePath,
                    174, // x position
                    5,   // y position
                    31,  // width
                    31,  // height
                    'PNG',
                    $this->packageUrl, // link
                    '',
                    false,
                    300,
                    '',
                    false,
                    false,
                    0,
                    false,
                    false,
                    true
                );

                // Add small text below QR code
                $this->SetTextColor(80, 80, 80);
                $this->SetFont('helvetica', '', 5);
                $this->SetXY(172, 35);
                $this->Cell(35, 2, 'Scan to view details', 0, 1, 'C');

                error_log('[PackagePDF][QR] ✓ QR code drawn successfully');

            } catch (\Throwable $e) {
                error_log('[PackagePDF][QR] ✗ Error drawing QR code: ' . $e->getMessage());
                $this->drawQrCodeFallback();
            }
        } else {
            error_log('[PackagePDF][QR] QR code file not found, using fallback');
            $this->drawQrCodeFallback();
        }
    }

    /**
     * Fallback UI when QR code is not available
     */
    private function drawQrCodeFallback(): void
    {
        if (!empty($this->packageUrl)) {
            // Show clickable URL
            $this->SetXY(172, 8);
            $this->SetFont('helvetica', 'B', 7);
            $this->SetTextColor(76, 175, 80);
            $this->Cell(35, 4, 'View Online:', 0, 1, 'C');

            $this->SetXY(172, 14);
            $this->SetFont('helvetica', 'U', 5);
            $this->SetTextColor(0, 0, 255);

            $this->writeHTMLCell(
                35, 15, 172, 14,
                '<a href="' . htmlspecialchars($this->packageUrl) . '" style="color: #0000FF; font-size: 5pt; text-align: center; display: block;" target="_blank">Click to view package</a>',
                0, 1, false, true, 'C'
            );
        } else {
            $this->SetTextColor(150, 150, 150);
            $this->SetFont('helvetica', '', 6);
            $this->SetXY(172, 15);
            $this->MultiCell(35, 4, "View package\nonline for\nmore details", 0, 'C');
        }
    }

    /**
     * Cleanup temporary QR code file
     */
    public function __destruct()
    {
        if (!empty($this->qrCodeImagePath) && file_exists($this->qrCodeImagePath)) {
            @unlink($this->qrCodeImagePath);
            error_log('[PackagePDF][QR] Cleaned up temp file: ' . $this->qrCodeImagePath);
        }
    }

    public function generateContent(): void
    {
        $this->AddPage();
        $this->drawHeader();
        $this->drawQrcode();
        $this->drawTitle();
        $this->drawPackageInfo();
        $this->drawImageGallery();
        $this->drawTourDescription();
        $this->drawDaywiseItinerary();
        $this->drawInclusionExclusion();
        $this->drawFaqTravelTips();
        $this->drawTermsAndCancellation();
        $this->drawFooter();
    }

    private function drawHeader(): void
    {
        // Green gradient background
        $this->SetFillColor(139, 195, 74);
        $this->RoundedRect(50, 0, 210, 40, 24, '1001', 'F');

        // Draw green line below header
        $this->SetDrawColor(139, 195, 74);
        $this->SetLineWidth(4);
        $this->Line(0, 40, 260, 40);

        // Logo
        if ($this->logoPath && file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 10, 8, 30, 0, '', '', '', false, 300);
        } else {
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('helvetica', 'B', 12);
            $this->SetXY(10, 12);
            $this->Cell(30, 8, 'LETS', 0, 0, 'C');
        }

        // Company name and tagline
        $this->SetTextColor(255, 255, 255);
        $this->SetFont('helvetica', 'B', 18);
        $this->SetXY(70, 10);
        $this->Cell(70, 7, 'LETS GO SIKKIM', 0, 1, 'C');

        $this->SetFont('helvetica', '', 12);
        $this->SetXY(70, 18);
        $this->Cell(70, 5, 'Your Gateway to the Himalayas', 0, 1, 'C');

        $this->SetFont('helvetica', '', 9);
        $this->SetXY(70, 25);
        $this->Cell(70, 4, 'Registration No. : 386/DoT&CAv/E/12/TA', 0, 1, 'C');

        if (!empty($this->packageUrl)) {
            $this->SetTextColor(255, 255, 255);
            $this->SetFont('helvetica', 'U', 9);
            $this->SetXY(70, 32);
            $this->writeHTMLCell(70, 4, 70, 32, '<a href="' . htmlspecialchars($this->packageUrl) . '" target="_blank" style="color:#FFFFFF;">View Package Details</a>', 0, 1, false, true, 'C');
        }
    }

    private function drawTitle(): void
    {
        $this->SetY(45);
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor(74, 6, 6);
        $this->Cell(0, 10, $this->vitinerary->getTitle(), 0, 1, 'C');
    }

    private function drawPackageInfo(): void
    {
        $y = $this->GetY() + 2;
        $price = $this->vitinerary->getPrice();
        $showPrice = $price && $price > 0;
        $margin = 8;
        $gap = 5;
        $totalWidth = 210 - (2 * $margin);
        $numBoxes = $showPrice ? 3 : 2;
        $boxWidth = ($totalWidth - ($gap * ($numBoxes - 1))) / $numBoxes;
        $boxHeight = 25;
        $x = $margin;

        // Duration Box
        $this->SetFillColor(245, 245, 245);
        $this->RoundedRect($x, $y, $boxWidth, $boxHeight, 2, '1111', 'F');
        $this->SetXY($x, $y + 6);
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColor(76, 121, 45);
        $duration = $this->vitinerary->getDuration() ?: 'N/A';
        $this->Cell($boxWidth, 6, 'Duration : [ ' . $duration . ' ]', 0, 1, 'C');
        $this->SetXY($x, $y + 14);
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(100, 100, 100);
        $tourTypes = $this->vitinerary->getTourTypes();
        $tourTypeStr = 'Cultural Tour';
        if ($tourTypes && count($tourTypes) > 0) {
            $types = [];
            foreach ($tourTypes as $t) {
                $types[] = method_exists($t, 'getTitle') ? $t->getTitle() : (string)$t;
            }
            $tourTypeStr = implode(', ', $types);
        }
        $this->MultiCell($boxWidth, 5, $tourTypeStr, 0, 'C');
        $x += $boxWidth + $gap;

        // Price Box
        if ($showPrice) {
            $this->SetFillColor(245, 245, 245);
            $this->RoundedRect($x, $y, $boxWidth, $boxHeight, 2, '1111', 'F');
            $this->SetXY($x, $y + 6);
            $this->SetFont('helvetica', 'B', 10);
            $this->SetTextColor(40, 125, 126);
            $this->Cell($boxWidth, 5, 'Starting from', 0, 1, 'C');
            $this->SetXY($x, $y + 13);
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor(51, 51, 51);
            $this->Cell($boxWidth, 6, 'Rs. ' . number_format($price) . '/-', 0, 0, 'C');
            $x += $boxWidth + $gap;
        }

        // Destination Box
        $this->SetFillColor(245, 245, 245);
        $this->RoundedRect($x, $y, $boxWidth, $boxHeight, 2, '1111', 'F');
        $this->SetXY($x, $y + 3);
        $this->SetFont('helvetica', 'B', 10);
        $this->SetTextColor(48, 95, 222);
        $this->Cell($boxWidth, 5, 'Destination Covered', 0, 1, 'C');
        $this->SetXY($x, $y + 10);
        $this->SetFont('helvetica', '', 9);
        $this->SetTextColor(51, 51, 51);
        $destinations = $this->vitinerary->getDestinations();
        $destStr = 'N/A';
        if ($destinations && count($destinations) > 0) {
            $dests = [];
            foreach ($destinations as $d) {
                $dests[] = method_exists($d, 'getTitle') ? $d->getTitle() : (string)$d;
            }
            $destStr = implode(', ', $dests);
        }
        $this->MultiCell($boxWidth, 4, $destStr, 0, 'C');
        $this->SetY($y + $boxHeight + 5);
    }

    private function drawImageGallery(): void
    {
        $startY = $this->GetY();
        $margin = 5;
        $cover = $this->vitinerary->getCoverImage();
        $allImages = $this->vitinerary->getImages() ?? [];
        $images = [];
        if ($cover) $images[] = $cover;
        foreach ($allImages as $img) {
            if ($img && $img !== $cover && count($images) < 6) $images[] = $img;
        }
        while (count($images) < 6) $images[] = null;

        $largeW = 120;
        $largeH = 90;
        $this->SetFillColor(240, 240, 240);
        $this->RoundedRect($margin, $startY, $largeW, $largeH, 2, '1111', 'F');

        $mainImg = $images[0];
        if ($mainImg) {
            $imgPath = $this->projectDir . '/public/uploads/image/' . $mainImg;
            if (file_exists($imgPath)) {
                $this->drawImageCover($imgPath, $margin + 1, $startY + 1, $largeW - 2, $largeH - 2);
            }
        }

        $smallW = 35;
        $smallH = 28;
        $gridGap = 3;
        $gridX = $margin + $largeW + 5;

        for ($i = 1; $i < 6; $i++) {
            $row = floor(($i - 1) / 2);
            $col = ($i - 1) % 2;
            $x = $gridX + ($col * ($smallW + $gridGap));
            $y = $startY + ($row * ($smallH + $gridGap));
            $this->SetFillColor(240, 240, 240);
            $this->RoundedRect($x, $y, $smallW, $smallH, 2, '1111', 'F');
            if (isset($images[$i]) && $images[$i]) {
                $imgPath = $this->projectDir . '/public/uploads/image/' . $images[$i];
                if (file_exists($imgPath)) {
                    $this->drawImageCover($imgPath, $x + 1, $y + 1, $smallW - 2, $smallH - 2);
                }
            }
        }

        $this->SetY($startY + $largeH + 8);
    }

    private function drawImageCover(string $path, float $x, float $y, float $boxW, float $boxH): void
    {
        try {
            [$imgW, $imgH] = getimagesize($path);
            $imgRatio = $imgW / $imgH;
            $boxRatio = $boxW / $boxH;
            $cropW = $imgW;
            $cropH = $imgH;
            $srcX = 0;
            $srcY = 0;

            if ($imgRatio > $boxRatio) {
                $cropW = (int)($imgH * $boxRatio);
                $srcX = (int)(($imgW - $cropW) / 2);
            } elseif ($imgRatio < $boxRatio) {
                $cropH = (int)($imgW / $boxRatio);
                $srcY = (int)(($imgH - $cropH) / 2);
            }

            $tmp = imagecreatetruecolor($cropW, $cropH);
            $src = imagecreatefromstring(file_get_contents($path));
            imagecopy($tmp, $src, 0, 0, $srcX, $srcY, $cropW, $cropH);
            $tmpPath = tempnam(sys_get_temp_dir(), 'img');
            imagejpeg($tmp, $tmpPath, 90);
            imagedestroy($tmp);
            imagedestroy($src);
            $this->Image($tmpPath, $x, $y, $boxW, $boxH, '', '', '', false, 300);
            @unlink($tmpPath);
        } catch (\Throwable $e) {
            error_log('[PackagePDF] Error drawing image: ' . $e->getMessage());
        }
    }

    private function drawTourDescription(): void
    {
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(52, 97, 101);
        $this->SetX(5);
        $this->Cell(0, 8, 'Tour Description', 0, 1, 'L');
        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(80, 80, 80);
        $desc = strip_tags($this->vitinerary->getDescription() ?: '');
        $this->MultiCell(190, 6, $desc, 0, 'L', false, 1, 5);
        $this->Ln(6);
    }

    private function drawDaywiseItinerary(): void
    {
        $this->SetFont('helvetica', 'B', 14);
        $this->SetTextColor(51, 51, 51);
        $this->SetX(5);
        $this->Cell(0, 8, 'Daywise Itinerary', 0, 1, 'L');
        $this->Ln(2);
        $days = $this->vitinerary->getDaywiseItinerary() ?? [];
        foreach ($days as $index => $day) {
            if ($this->GetY() > 250) $this->AddPage();
            $title = $day['title'] ?? 'Day ' . ($index + 1);
            $desc = strip_tags($day['description'] ?? '');
            $this->SetFillColor(255, 255, 255);
            $this->SetTextColor(76, 121, 45);
            $this->SetFont('helvetica', 'B', 11);
            $this->SetX(5);
            $this->Cell(190, 7, 'Day ' . ($index + 1) . '. ' . $title, 0, 1, 'L', true);
            $this->SetFont('helvetica', '', 10);
            $this->SetTextColor(80, 80, 80);
            $this->MultiCell(190, 5, $desc, 0, 'L', false, 1, 8);
            $this->Ln(5);
        }
        $this->Ln(3);
    }

    private function drawInclusionExclusion(): void
    {
        $includes = $this->vitinerary->getInclude() ?? [];
        $excludes = $this->vitinerary->getExclude() ?? [];
        if (empty($includes) && empty($excludes)) return;
        $startY = $this->GetY();
        $margin = 10;
        $boxWidth = 93;
        $gap = 5;

        if (!empty($includes)) {
            $this->SetFillColor(232, 245, 233);
            $this->RoundedRect($margin, $startY, $boxWidth, 50, 3, '1111', 'F');
            $this->SetXY($margin + 5, $startY + 5);
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor(76, 175, 80);
            $this->Cell($boxWidth - 10, 6, 'Inclusion', 0, 1, 'L');
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(60, 60, 60);
            $yPos = $startY + 13;
            foreach (array_slice($includes, 0, 8) as $inc) {
                $this->SetXY($margin + 5, $yPos);
                $this->MultiCell($boxWidth - 10, 4, '• ' . $inc, 0, 'L');
                $yPos = $this->GetY() + 1;
            }
        }

        if (!empty($excludes)) {
            $xPos = $margin + $boxWidth + $gap;
            $this->SetFillColor(255, 235, 238);
            $this->RoundedRect($xPos, $startY, $boxWidth, 50, 3, '1111', 'F');
            $this->SetXY($xPos + 5, $startY + 5);
            $this->SetFont('helvetica', 'B', 12);
            $this->SetTextColor(244, 67, 54);
            $this->Cell($boxWidth - 10, 6, 'Exclusion', 0, 1, 'L');
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(60, 60, 60);
            $yPos = $startY + 13;
            foreach (array_slice($excludes, 0, 8) as $exc) {
                $this->SetXY($xPos + 5, $yPos);
                $this->MultiCell($boxWidth - 10, 4, '• ' . $exc, 0, 'L');
                $yPos = $this->GetY() + 1;
            }
        }

        $this->SetY($startY + 55);
    }

    private function drawFaqTravelTips(): void
    {
        $startY = $this->GetY();
        $margin = 10;
        $boxWidth = 190;
        $faqs = $this->vitinerary->getFaq() ?? [];
        if (!empty($faqs)) {
            $this->SetFillColor(227, 242, 253);
            $boxHeight = max(40, count($faqs) * 8);
            $this->RoundedRect($margin, $startY, $boxWidth, $boxHeight, 3, '1111', 'F');
            $this->SetXY($margin + 5, $startY + 5);
            $this->SetFont('helvetica', 'B', 11);
            $this->SetTextColor(33, 150, 243);
            $this->Cell($boxWidth - 10, 6, "FAQ's", 0, 1, 'L');
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(60, 60, 60);
            foreach (array_slice($faqs, 0, 6) as $faq) {
                $this->SetX($margin + 5);
                if (is_array($faq)) {
                    $q = $faq['question'] ?? '';
                    $this->MultiCell($boxWidth - 10, 4, '• ' . $q, 0, 'L');
                } else {
                    $this->MultiCell($boxWidth - 10, 4, '• ' . $faq, 0, 'L');
                }
                $this->Ln(1);
            }
            $startY = $this->GetY() + 5;
        }

        $tips = [];
        $tipsStr = $this->vitinerary->getTravelTips();
        if ($tipsStr) {
            if (is_string($tipsStr)) {
                $tips = preg_split('/\r\n|\r|\n|•/', $tipsStr);
                $tips = array_filter(array_map('trim', $tips));
            } elseif (is_array($tipsStr)) {
                $tips = $tipsStr;
            }
        }

        if (!empty($tips)) {
            $boxHeight = max(40, count($tips) * 6);
            $this->SetFillColor(232, 245, 233);
            $this->RoundedRect($margin, $startY, $boxWidth, $boxHeight, 3, '1111', 'F');
            $this->SetXY($margin + 5, $startY + 5);
            $this->SetFont('helvetica', 'B', 11);
            $this->SetTextColor(76, 175, 80);
            $this->Cell($boxWidth - 10, 6, 'Travel Tips', 0, 1, 'L');
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(60, 60, 60);
            foreach (array_slice($tips, 0, 6) as $tip) {
                if (empty(trim($tip))) continue;
                $this->SetX($margin + 5);
                $this->MultiCell($boxWidth - 10, 4, '• ' . $tip, 0, 'L');
                $this->Ln(1);
            }
            $startY = $this->GetY() + 5;
        }

        $this->SetY($startY);
    }

    private function drawTermsAndCancellation(): void
    {
        $terms = $this->vitinerary->getTermsAndCondition();
        $cancel = $this->vitinerary->getCancellation();

        if ($terms) {
            $this->SetFont('helvetica', 'B', 11);
            $this->SetTextColor(51, 51, 11);
            $this->SetX(15);
            $this->Cell(0, 0, 'Terms and Conditions', 0, 1, 'L', false, 1, 15);
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(80, 80, 80);
            $this->MultiCell(180, 4, strip_tags($terms), 0, 'L', false, 1, 15);
            $this->Ln(8);
        }

        if ($cancel) {
            $this->SetFont('helvetica', 'B', 11);
            $this->SetTextColor(51, 51, 51);
            $this->SetX(10);
            $this->Cell(0, 0, 'Cancellation Policy', 0, 1, 'L');
            $this->SetFont('helvetica', '', 8);
            $this->SetTextColor(80, 80, 80);
            $this->MultiCell(190, 4, strip_tags($cancel), 0, 'L', false, 1, 10);
            $this->Ln(7);
        }
    }


    private function drawFooter(): void
    {
        $footerY = 255;      // Adjust height
        $footerHeight = 45;  // More padding

        // Wave Fill Color
        $this->SetFillColor(58,60,62);

        // Start drawing path
        $this->SetDrawColor(58,60,62);
        $this->SetLineStyle(['width' => 12]);

        $this->StartTransform();
        $this->SetY($footerY);

        // Begin shape
        $this->Cell(0, 0, '', 0, 1);
        $this->Curve(0, $footerY + 15, 70, $footerY - 5, 140, $footerY + 35, 210, $footerY + 10);

        // Close bottom rectangle of shape
        $this->Line(210, $footerY + $footerHeight, 0, $footerY + $footerHeight);
        $this->Line(0, $footerY + $footerHeight, 0, $footerY + 15);

        $this->Rect(0, $footerY + 15, 210, $footerHeight - 15, 'F');

        $this->StopTransform();


        $this->SetTextColor(0, 102, 102);
        $this->SetFont('helvetica', 'B', 14);
        $this->SetXY(50, $footerY + 1);
        $this->Cell(210, 8, 'Ready to Book Your Adventure ?', 0, 1, 'C');

        if ($this->contact) {
            $this->SetFont('helvetica', '', 11);
            $this->SetXY(10, $footerY + 18);

            $phone = $this->contact->getPhone1() ?: '';
            $this->SetTextColor(255, 255, 255);
            $website = $this->contact->getSitelink() ?: 'www.letsgosikkim.com';
            $email = $this->contact->getEmail1() ?: '';

            $this->Cell(60, 5, 'Phone: ' . $phone, 0, 0, 'L');
            $this->SetXY(75, $footerY + 18);
            $this->Cell(60, 5, $website, 0, 0, 'C');
            $this->SetXY(140, $footerY + 18);
            $this->Cell(60, 5, 'Email: ' . $email, 0, 0, 'R');

            $this->SetFont('helvetica', '', 8);
            $this->SetXY(10, $footerY + 32);
            $this->MultiCell(190, 4, $this->contact->getAddress() ?: '', 0, 'C');
        }
    }

}
