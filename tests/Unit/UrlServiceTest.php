<?php

namespace Tests\Unit;

use App\Exceptions\AnalisisException;
use App\Services\UrlService;
use PHPUnit\Framework\TestCase;

class UrlServiceTest extends TestCase
{
    public function test_normaliza_url(): void
    {
        $s = new UrlService();
        $this->assertSame('http://example.com/a?x=1', $s->normalizar('  EXAMPLE.com/a?x=1#frag '));
        $this->assertSame('https://example.com/', $s->normalizar('https://example.com:443'));
    }

    public function test_rechaza_urls_invalidas(): void
    {
        $s = new UrlService();
        foreach (['javascript:alert(1)', 'ftp://example.com', 'http://user:pw@example.com', 'hola mundo', ''] as $malo) {
            try {
                $s->normalizar($malo);
                $this->fail("Debió rechazar: $malo");
            } catch (AnalisisException $e) {
                $this->assertTrue(true);
            }
        }
    }

    public function test_detecta_acortadores_e_ips_privadas(): void
    {
        $s = new UrlService();
        $this->assertTrue($s->esAcortado('https://bit.ly/abc'));
        $this->assertFalse($s->esAcortado('https://example.com/'));
        $this->assertFalse($s->ipPublica('127.0.0.1'));
        $this->assertFalse($s->ipPublica('192.168.1.10'));
        $this->assertFalse($s->ipPublica('169.254.169.254'));
        $this->assertTrue($s->ipPublica('8.8.8.8'));
    }
}
