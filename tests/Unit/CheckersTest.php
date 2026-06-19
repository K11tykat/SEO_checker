<?php

namespace Tests\Unit;

use App\Services\Seo\Checkers\DescriptionChecker;
use App\Services\Seo\Checkers\HeadingChecker;
use App\Services\Seo\Checkers\LinksChecker;
use App\Services\Seo\Checkers\MicrodataChecker;
use App\Services\Seo\Checkers\TitleChecker;
use App\Services\Seo\ResultNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DomCrawler\Crawler;

class CheckersTest extends TestCase
{
    private function crawler(string $html): Crawler
    {
        return new Crawler($html);
    }

    public function test_title_green_for_single_tag(): void
    {
        $result = (new TitleChecker)->check($this->crawler('<html><head><title>Привет</title></head></html>'));

        $this->assertSame('green', $result['marker']);
        $this->assertSame(6, $result['length']);
        $this->assertNull($result['error']);
    }

    public function test_title_red_when_missing(): void
    {
        $result = (new TitleChecker)->check($this->crawler('<html><head></head></html>'));

        $this->assertSame('red', $result['marker']);
        $this->assertNotNull($result['error']);
    }

    public function test_description_red_when_duplicated(): void
    {
        $html = '<head><meta name="description" content="a"><meta name="description" content="b"></head>';
        $result = (new DescriptionChecker)->check($this->crawler($html));

        $this->assertSame('red', $result['marker']);
    }

    public function test_heading_structure_valid(): void
    {
        $html = '<body><h1>A</h1><h2>B</h2><h3>C</h3></body>';
        $result = (new HeadingChecker)->check($this->crawler($html));

        $this->assertSame('green', $result['h1']['marker']);
        $this->assertSame('green', $result['structure']['marker']);
    }

    public function test_heading_structure_invalid_on_skip(): void
    {
        $html = '<body><h1>A</h1><h4>B</h4></body>';
        $result = (new HeadingChecker)->check($this->crawler($html));

        $this->assertSame('red', $result['structure']['marker']);
    }

    public function test_links_counts_external_and_nofollow(): void
    {
        $html = '<body>'
            .'<a href="https://other.com/a">ext</a>'
            .'<a href="https://other.com/b" rel="nofollow">ext-nofollow</a>'
            .'<a href="/internal">int</a>'
            .'</body>';
        $result = (new LinksChecker)->check($this->crawler($html), 'https://mysite.com');

        $this->assertSame(2, $result['external_links_count']);
        $this->assertSame(1, $result['nofollow_count']);
        $this->assertSame(1, $result['dofollow_count']);
    }

    public function test_microdata_detects_og_and_schema(): void
    {
        $html = '<head>'
            .'<meta property="og:title" content="x">'
            .'<script type="application/ld+json">{}</script>'
            .'</head>';
        $result = (new MicrodataChecker)->check($this->crawler($html));

        $this->assertSame('green', $result['open_graph']['marker']);
        $this->assertSame('green', $result['schema_org']['marker']);
        $this->assertContains('JSON-LD', $result['schema_org']['formats']);
    }

    public function test_normalizer_maps_checker_output_to_storage_shape(): void
    {
        $pageData = ['success' => true, 'status_code' => 200, 'final_url' => 'https://site.com'];
        $checks = [
            'title' => ['marker' => 'green', 'length' => 10, 'error' => null],
            'description' => ['marker' => 'red', 'length' => 0, 'error' => 'нет'],
            'heading' => [
                'h1' => ['marker' => 'green', 'error' => null],
                'structure' => ['marker' => 'green', 'error' => null],
            ],
            'links' => ['external_links_count' => 3, 'nofollow_count' => 1, 'dofollow_count' => 2],
            'microdata' => [
                'open_graph' => ['marker' => 'green', 'error' => null],
                'schema_org' => ['marker' => 'green', 'formats' => ['JSON-LD'], 'error' => null],
            ],
            'robots' => ['marker' => 'green', 'error' => null],
            'sitemap' => ['marker' => 'red', 'error' => 'нет'],
        ];

        $data = (new ResultNormalizer)->normalize('https://site.com', $pageData, $checks);

        $this->assertSame(200, $data['http_code']);
        $this->assertTrue($data['title']['valid']);
        $this->assertSame(10, $data['title']['length']);
        $this->assertFalse($data['description']['valid']);
        $this->assertTrue($data['headings_valid']);
        $this->assertSame(1, $data['external_links_nofollow']);
        $this->assertTrue($data['og_marker']);
        $this->assertSame(['JSON-LD'], $data['schema_formats']);
        $this->assertFalse($data['sitemap_marker']);
    }
}
