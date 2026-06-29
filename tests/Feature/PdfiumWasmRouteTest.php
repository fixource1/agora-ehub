<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfiumWasmRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdfium_wasm_route_serves_webassembly_binary(): void
    {
        $path = public_path('pdf/pdfium.wasm');

        if (! is_readable($path)) {
            $this->markTestSkipped('public/pdf/pdfium.wasm is not present. Run npm run build first.');
        }

        $response = $this->get('/pdf/pdfium.wasm');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/wasm');

        $bytes = $response->getContent();

        $this->assertSame("\0asm", substr($bytes, 0, 4));
    }
}
