<?php

namespace App\Support;

/**
 * Generator QR Code SVG standar ISO/IEC 18004 (Byte Mode, ECC Level M, Versi 1–6)
 * tanpa dependensi pihak ketiga agar ringan dan bebas konflik composer lintas-branch.
 *
 * Mendukung payload hingga 106 byte (cukup untuk URL scan meja KuponGo).
 */
class QrCodeSvgGenerator
{
    /**
     * Spesifikasi Versi 1–6 pada Error Correction Level M (uniform block sizes):
     * [size, dataCodewords, eccPerBlock, blocks, alignCenters]
     *
     * @var array<int, array{0:int, 1:int, 2:int, 3:int, 4:list<int>}>
     */
    private const VERSION_SPEC = [
        1 => [21, 16, 10, 1, []],
        2 => [25, 28, 16, 1, [6, 18]],
        3 => [29, 44, 26, 1, [6, 22]],
        4 => [33, 64, 18, 2, [6, 26]],
        5 => [37, 86, 24, 2, [6, 30]],
        6 => [41, 108, 16, 4, [6, 34]],
    ];

    /**
     * Menghasilkan matriks QR boolean (true = modul gelap, false = modul terang).
     *
     * @return array<int, array<int, bool>>
     */
    public function encodeMatrix(string $text): array
    {
        $bytes = array_values(unpack('C*', $text) ?: []);
        $length = count($bytes);

        $version = $this->selectVersion($length);
        [$size, $totalDataCodewords, $eccPerBlock, $numBlocks, $alignCenters] = self::VERSION_SPEC[$version];

        $dataCodewords = $this->buildDataCodewords($bytes, $totalDataCodewords);
        $finalCodewords = $this->interleaveWithEcc($dataCodewords, $eccPerBlock, $numBlocks);

        // Inisialisasi matriks dan peta fungsi (reserved)
        $matrix = array_fill(0, $size, array_fill(0, $size, false));
        $isFunction = array_fill(0, $size, array_fill(0, $size, false));

        $this->drawFunctionPatterns($matrix, $isFunction, $size, $version, $alignCenters);
        $this->placeDataBits($matrix, $isFunction, $size, $finalCodewords);

        // Pilih mask optimal (0..7) dengan penalti terendah
        $bestMask = 0;
        $bestPenalty = PHP_INT_MAX;
        $bestMatrix = $matrix;

        for ($mask = 0; $mask < 8; $mask++) {
            $candidate = $this->applyMask($matrix, $isFunction, $size, $mask);
            $this->drawFormatBits($candidate, $size, $mask);
            $penalty = $this->calculatePenalty($candidate, $size);

            if ($penalty < $bestPenalty) {
                $bestPenalty = $penalty;
                $bestMask = $mask;
                $bestMatrix = $candidate;
            }
        }

        $this->drawFormatBits($bestMatrix, $size, $bestMask);

        return $bestMatrix;
    }

    /**
     * Merender QR Code menjadi string SVG standalone dengan quiet zone 4 modul.
     */
    public function renderSvg(
        string $text,
        int $pixelSize = 240,
        string $darkColor = '#103B2C',
        string $lightColor = '#FFFFFF'
    ): string {
        $matrix = $this->encodeMatrix($text);
        $size = count($matrix);
        $quiet = 4;
        $viewSize = $size + ($quiet * 2);

        $pathParts = [];
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $c + $quiet;
                    $y = $r + $quiet;
                    $pathParts[] = "M{$x},{$y}h1v1h-1z";
                }
            }
        }

        $pathData = implode('', $pathParts);

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%d" height="%d" viewBox="0 0 %d %d" shape-rendering="crispEdges" role="img" aria-label="QR Code Meja KuponGo">'
            .'<rect width="100%%" height="100%%" fill="%s"/>'
            .'<path d="%s" fill="%s"/>'
            .'</svg>',
            $pixelSize,
            $pixelSize,
            $viewSize,
            $viewSize,
            htmlspecialchars($lightColor, ENT_QUOTES, 'UTF-8'),
            $pathData,
            htmlspecialchars($darkColor, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Merender elemen <g> berisi QR Code untuk disematkan ke dalam SVG stiker cetak.
     */
    public function renderEmbeddedGroup(
        string $text,
        float $offsetX,
        float $offsetY,
        float $targetWidth,
        string $darkColor = '#103B2C'
    ): string {
        $matrix = $this->encodeMatrix($text);
        $size = count($matrix);
        $quiet = 4;
        $totalModules = $size + ($quiet * 2);
        $scale = $targetWidth / $totalModules;

        $pathParts = [];
        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($matrix[$r][$c]) {
                    $x = $c + $quiet;
                    $y = $r + $quiet;
                    $pathParts[] = "M{$x},{$y}h1v1h-1z";
                }
            }
        }

        return sprintf(
            '<g transform="translate(%.2f, %.2f) scale(%.6f)" shape-rendering="crispEdges">'
            .'<rect width="%d" height="%d" fill="#FFFFFF"/>'
            .'<path d="%s" fill="%s"/>'
            .'</g>',
            $offsetX,
            $offsetY,
            $scale,
            $totalModules,
            $totalModules,
            implode('', $pathParts),
            htmlspecialchars($darkColor, ENT_QUOTES, 'UTF-8')
        );
    }

    private function selectVersion(int $byteLength): int
    {
        foreach (self::VERSION_SPEC as $version => $spec) {
            // Byte mode header = 4 bit mode + 8 bit length = 12 bit -> max bytes = dataCodewords - 2
            if ($byteLength <= ($spec[1] - 2)) {
                return $version;
            }
        }

        throw new \InvalidArgumentException('Payload QR terlalu panjang untuk versi 1–6 ECC-M (maksimal 106 byte).');
    }

    /**
     * @param  list<int>  $bytes
     * @return list<int>
     */
    private function buildDataCodewords(array $bytes, int $totalDataCodewords): array
    {
        $bits = [];
        // Mode indicator: Byte mode (0100)
        $this->appendBits($bits, 0b0100, 4);
        // Character count indicator (8 bits for Versions 1–9)
        $this->appendBits($bits, count($bytes), 8);

        foreach ($bytes as $b) {
            $this->appendBits($bits, $b, 8);
        }

        $capacityBits = $totalDataCodewords * 8;
        $terminatorLen = min(4, $capacityBits - count($bits));
        $this->appendBits($bits, 0, $terminatorLen);

        // Pad ke kelipatan 8 bit
        while (count($bits) % 8 !== 0) {
            $bits[] = 0;
        }

        // Konversi bit ke byte
        $codewords = [];
        $bitCount = count($bits);
        for ($i = 0; $i < $bitCount; $i += 8) {
            $val = 0;
            for ($j = 0; $j < 8; $j++) {
                $val = ($val << 1) | $bits[$i + $j];
            }
            $codewords[] = $val;
        }

        // Pad bytes bergantian 0xEC dan 0x11
        $padBytes = [0xEC, 0x11];
        $padIdx = 0;
        while (count($codewords) < $totalDataCodewords) {
            $codewords[] = $padBytes[$padIdx % 2];
            $padIdx++;
        }

        return $codewords;
    }

    /**
     * @param  list<int>  $bits
     */
    private function appendBits(array &$bits, int $value, int $length): void
    {
        for ($i = $length - 1; $i >= 0; $i--) {
            $bits[] = ($value >> $i) & 1;
        }
    }

    /**
     * @param  list<int>  $dataCodewords
     * @return list<int>
     */
    private function interleaveWithEcc(array $dataCodewords, int $eccPerBlock, int $numBlocks): array
    {
        $blockDataSize = intdiv(count($dataCodewords), $numBlocks);
        $generator = $this->rsGeneratorPoly($eccPerBlock);

        $dataBlocks = [];
        $eccBlocks = [];

        for ($b = 0; $b < $numBlocks; $b++) {
            $block = array_slice($dataCodewords, $b * $blockDataSize, $blockDataSize);
            $dataBlocks[] = $block;
            $eccBlocks[] = $this->rsComputeRemainder($block, $generator);
        }

        $result = [];
        for ($i = 0; $i < $blockDataSize; $i++) {
            for ($b = 0; $b < $numBlocks; $b++) {
                $result[] = $dataBlocks[$b][$i];
            }
        }
        for ($i = 0; $i < $eccPerBlock; $i++) {
            for ($b = 0; $b < $numBlocks; $b++) {
                $result[] = $eccBlocks[$b][$i];
            }
        }

        return $result;
    }

    /**
     * @return list<int>
     */
    private function rsGeneratorPoly(int $degree): array
    {
        $poly = [1];
        $root = 1;

        for ($i = 0; $i < $degree; $i++) {
            $next = array_fill(0, count($poly) + 1, 0);
            for ($j = 0; $j < count($poly); $j++) {
                $next[$j] ^= $poly[$j];
                $next[$j + 1] ^= $this->gfMultiply($poly[$j], $root);
            }
            $poly = $next;
            $root = $this->gfMultiply($root, 0x02);
        }

        // Hapus koefisien tertinggi (selalu 1) agar panjangnya tepat $degree
        array_shift($poly);

        return $poly;
    }

    /**
     * @param  list<int>  $data
     * @param  list<int>  $generator
     * @return list<int>
     */
    private function rsComputeRemainder(array $data, array $generator): array
    {
        $degree = count($generator);
        $remainder = array_fill(0, $degree, 0);

        foreach ($data as $byte) {
            $factor = $byte ^ array_shift($remainder);
            $remainder[] = 0;
            for ($i = 0; $i < $degree; $i++) {
                $remainder[$i] ^= $this->gfMultiply($generator[$i], $factor);
            }
        }

        return $remainder;
    }

    private function gfMultiply(int $a, int $b): int
    {
        $result = 0;
        for ($i = 7; $i >= 0; $i--) {
            $result = ($result << 1) ^ (($result >> 7) * 0x11D);
            if ((($b >> $i) & 1) !== 0) {
                $result ^= $a;
            }
        }

        return $result;
    }

    /**
     * @param  array<int, array<int, bool>>  $matrix
     * @param  array<int, array<int, bool>>  $isFunction
     * @param  list<int>  $alignCenters
     */
    private function drawFunctionPatterns(
        array &$matrix,
        array &$isFunction,
        int $size,
        int $version,
        array $alignCenters
    ): void {
        // Timing patterns
        for ($i = 0; $i < $size; $i++) {
            $this->setFunctionModule($matrix, $isFunction, 6, $i, $i % 2 === 0);
            $this->setFunctionModule($matrix, $isFunction, $i, 6, $i % 2 === 0);
        }

        // Finder patterns (3 sudut)
        $this->drawFinderPattern($matrix, $isFunction, $size, 3, 3);
        $this->drawFinderPattern($matrix, $isFunction, $size, 3, $size - 4);
        $this->drawFinderPattern($matrix, $isFunction, $size, $size - 4, 3);

        // Alignment patterns
        $count = count($alignCenters);
        for ($i = 0; $i < $count; $i++) {
            for ($j = 0; $j < $count; $j++) {
                // Lewati 3 sudut yang sudah ditempati finder pattern
                if (($i === 0 && $j === 0) || ($i === 0 && $j === $count - 1) || ($i === $count - 1 && $j === 0)) {
                    continue;
                }
                $this->drawAlignmentPattern($matrix, $isFunction, $alignCenters[$i], $alignCenters[$j]);
            }
        }

        // Reserve area format info (akan diisi ulang saat drawFormatBits)
        for ($i = 0; $i <= 5; $i++) {
            $this->setFunctionModule($matrix, $isFunction, 8, $i, false);
            $this->setFunctionModule($matrix, $isFunction, $i, 8, false);
        }
        $this->setFunctionModule($matrix, $isFunction, 8, 7, false);
        $this->setFunctionModule($matrix, $isFunction, 8, 8, false);
        $this->setFunctionModule($matrix, $isFunction, 7, 8, false);

        for ($i = 0; $i < 8; $i++) {
            $this->setFunctionModule($matrix, $isFunction, 8, $size - 1 - $i, false);
        }
        for ($i = 0; $i < 7; $i++) {
            $this->setFunctionModule($matrix, $isFunction, $size - 1 - $i, 8, false);
        }

        // Dark module tetap
        $this->setFunctionModule($matrix, $isFunction, (4 * $version) + 9, 8, true);
    }

    /**
     * @param  array<int, array<int, bool>>  $matrix
     * @param  array<int, array<int, bool>>  $isFunction
     */
    private function drawFinderPattern(array &$matrix, array &$isFunction, int $size, int $centerR, int $centerC): void
    {
        for ($dr = -4; $dr <= 4; $dr++) {
            for ($dc = -4; $dc <= 4; $dc++) {
                $r = $centerR + $dr;
                $c = $centerC + $dc;
                if ($r >= 0 && $r < $size && $c >= 0 && $c < $size) {
                    $dist = max(abs($dr), abs($dc));
                    $dark = ($dist !== 2 && $dist !== 4);
                    $this->setFunctionModule($matrix, $isFunction, $r, $c, $dark);
                }
            }
        }
    }

    /**
     * @param  array<int, array<int, bool>>  $matrix
     * @param  array<int, array<int, bool>>  $isFunction
     */
    private function drawAlignmentPattern(array &$matrix, array &$isFunction, int $centerR, int $centerC): void
    {
        for ($dr = -2; $dr <= 2; $dr++) {
            for ($dc = -2; $dc <= 2; $dc++) {
                $dist = max(abs($dr), abs($dc));
                $this->setFunctionModule($matrix, $isFunction, $centerR + $dr, $centerC + $dc, $dist !== 1);
            }
        }
    }

    /**
     * @param  array<int, array<int, bool>>  $matrix
     * @param  array<int, array<int, bool>>  $isFunction
     */
    private function setFunctionModule(array &$matrix, array &$isFunction, int $r, int $c, bool $dark): void
    {
        $matrix[$r][$c] = $dark;
        $isFunction[$r][$c] = true;
    }

    /**
     * @param  array<int, array<int, bool>>  $matrix
     * @param  array<int, array<int, bool>>  $isFunction
     * @param  list<int>  $codewords
     */
    private function placeDataBits(array &$matrix, array $isFunction, int $size, array $codewords): void
    {
        $bitIndex = 0;
        $totalBits = count($codewords) * 8;
        $upward = true;

        for ($right = $size - 1; $right >= 1; $right -= 2) {
            if ($right === 6) {
                $right = 5;
            }

            for ($vert = 0; $vert < $size; $vert++) {
                $r = $upward ? ($size - 1 - $vert) : $vert;

                for ($j = 0; $j < 2; $j++) {
                    $c = $right - $j;
                    if (! $isFunction[$r][$c]) {
                        $dark = false;
                        if ($bitIndex < $totalBits) {
                            $byte = $codewords[$bitIndex >> 3];
                            $dark = (($byte >> (7 - ($bitIndex & 7))) & 1) !== 0;
                            $bitIndex++;
                        }
                        $matrix[$r][$c] = $dark;
                    }
                }
            }

            $upward = ! $upward;
        }
    }

    /**
     * @param  array<int, array<int, bool>>  $matrix
     * @param  array<int, array<int, bool>>  $isFunction
     * @return array<int, array<int, bool>>
     */
    private function applyMask(array $matrix, array $isFunction, int $size, int $mask): array
    {
        $result = $matrix;

        for ($r = 0; $r < $size; $r++) {
            for ($c = 0; $c < $size; $c++) {
                if ($isFunction[$r][$c]) {
                    continue;
                }

                $invert = match ($mask) {
                    0 => ($r + $c) % 2 === 0,
                    1 => $r % 2 === 0,
                    2 => $c % 3 === 0,
                    3 => ($r + $c) % 3 === 0,
                    4 => (intdiv($r, 2) + intdiv($c, 3)) % 2 === 0,
                    5 => (($r * $c) % 2) + (($r * $c) % 3) === 0,
                    6 => ((($r * $c) % 2) + (($r * $c) % 3)) % 2 === 0,
                    7 => ((($r + $c) % 2) + (($r * $c) % 3)) % 2 === 0,
                    default => false,
                };

                if ($invert) {
                    $result[$r][$c] = ! $result[$r][$c];
                }
            }
        }

        return $result;
    }

    /**
     * Menulis 15-bit format information (ECC Level M = 00 + mask 3-bit).
     *
     * @param  array<int, array<int, bool>>  $matrix
     */
    private function drawFormatBits(array &$matrix, int $size, int $mask): void
    {
        // ECC Level M format indicator bits = 00
        $data = (0b00 << 3) | $mask;
        $rem = $data;
        for ($i = 0; $i < 10; $i++) {
            $rem = ($rem << 1) ^ (($rem >> 9) * 0x537);
        }
        $bits = (($data << 10) | $rem) ^ 0x5412;

        // Salinan pertama (sekitar top-left)
        for ($i = 0; $i <= 5; $i++) {
            $matrix[8][$i] = (($bits >> (14 - $i)) & 1) !== 0;
        }
        $matrix[8][7] = (($bits >> 8) & 1) !== 0;
        $matrix[8][8] = (($bits >> 7) & 1) !== 0;
        $matrix[7][8] = (($bits >> 6) & 1) !== 0;
        for ($i = 9; $i < 15; $i++) {
            $matrix[14 - $i][8] = (($bits >> (14 - $i)) & 1) !== 0;
        }

        // Salinan kedua (bottom-left & top-right)
        for ($i = 0; $i < 8; $i++) {
            $matrix[8][$size - 1 - $i] = (($bits >> $i) & 1) !== 0;
        }
        for ($i = 0; $i < 7; $i++) {
            $matrix[$size - 1 - $i][8] = (($bits >> (14 - $i)) & 1) !== 0;
        }
    }

    /**
     * Menghitung penalti dasar (run-length & 2x2 blocks) untuk pemilihan mask.
     *
     * @param  array<int, array<int, bool>>  $matrix
     */
    private function calculatePenalty(array $matrix, int $size): int
    {
        $penalty = 0;

        // Penalti baris & kolom berturut-turut >= 5
        for ($r = 0; $r < $size; $r++) {
            $runColor = $matrix[$r][0];
            $runLen = 1;
            for ($c = 1; $c < $size; $c++) {
                if ($matrix[$r][$c] === $runColor) {
                    $runLen++;
                    if ($runLen === 5) {
                        $penalty += 3;
                    } elseif ($runLen > 5) {
                        $penalty += 1;
                    }
                } else {
                    $runColor = $matrix[$r][$c];
                    $runLen = 1;
                }
            }
        }

        for ($c = 0; $c < $size; $c++) {
            $runColor = $matrix[0][$c];
            $runLen = 1;
            for ($r = 1; $r < $size; $r++) {
                if ($matrix[$r][$c] === $runColor) {
                    $runLen++;
                    if ($runLen === 5) {
                        $penalty += 3;
                    } elseif ($runLen > 5) {
                        $penalty += 1;
                    }
                } else {
                    $runColor = $matrix[$r][$c];
                    $runLen = 1;
                }
            }
        }

        // Penalti blok 2x2 warna sama
        for ($r = 0; $r < $size - 1; $r++) {
            for ($c = 0; $c < $size - 1; $c++) {
                $color = $matrix[$r][$c];
                if ($color === $matrix[$r][$c + 1] && $color === $matrix[$r + 1][$c] && $color === $matrix[$r + 1][$c + 1]) {
                    $penalty += 3;
                }
            }
        }

        return $penalty;
    }
}
