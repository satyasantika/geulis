<?php

namespace App\Services\Simulasi;

use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * SVG QR di-host sendiri (tanpa CDN). Paket chillerlan/php-qrcode lisensi MIT:
 * encoder QR bukan klaim HKI GEULIS, dan menulis encoder sendiri rawan salah pindai.
 */
final class QrSvg
{
    public function dariUrl(string $url): string
    {
        $opsi = new QROptions;
        $opsi->outputType = QROutputInterface::MARKUP_SVG;
        $opsi->scale = 6;
        $opsi->addQuietzone = true;
        $opsi->svgAddXmlHeader = false;

        return (new QRCode($opsi))->render($url);
    }
}
