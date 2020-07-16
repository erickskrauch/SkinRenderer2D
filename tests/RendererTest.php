<?php
namespace ErickSkrauch\SkinRenderer2D\Tests;

use ErickSkrauch\SkinRenderer2D\Renderer;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

class RendererTest extends TestCase {
    use MatchesSnapshots {
        assertMatchesSnapshot as _assertMatchesSnapshot;
    }

    /**
     * @var PNGResourceDriver
     */
    private $driver;

    protected function setUp(): void {
        parent::setUp();
        $this->driver = new PNGResourceDriver();
    }

    /**
     * @dataProvider renderFaceCases
     */
    public function testRenderFace(Renderer $renderer, int $zoom): void {
        $this->assertMatchesSnapshot($renderer->renderFace($zoom));
    }

    public function renderFaceCases(): iterable {
        yield 'zoom x1' => [Renderer::assignSkinFromFile(__DIR__ . '/skins/default.png'), 1];
        yield 'zoom x10' => [Renderer::assignSkinFromFile(__DIR__ . '/skins/default.png'), 10];
    }

    /**
     * @dataProvider renderSkinCases
     */
    public function testRenderFront(Renderer $renderer, int $zoom, int $r = null, int $g = null, int $b = null): void {
        $this->assertMatchesSnapshot($renderer->renderFront($zoom, $r, $g, $b));
    }

    /**
     * @dataProvider renderSkinCases
     */
    public function testRenderBack(Renderer $renderer, int $zoom, int $r = null, int $g = null, int $b = null): void {
        $this->assertMatchesSnapshot($renderer->renderBack($zoom, $r, $g, $b));
    }

    /**
     * @dataProvider renderSkinCases
     */
    public function testRenderCombined(Renderer $renderer, int $zoom, int $r = null, int $g = null, int $b = null): void {
        $this->assertMatchesSnapshot($renderer->renderCombined($zoom, $r, $g, $b));
    }

    public function renderSkinCases(): iterable {
        yield 'default skin, zoom x1' => [Renderer::assignSkinFromFile(__DIR__ . '/skins/default.png'), 1];
        yield 'modern skin, zoom x10, background' => [Renderer::assignSkinFromFile(__DIR__ . '/skins/modern.png'), 10, 0, 0, 0];
        yield 'slim skin, zoom x5' => [Renderer::assignSkinFromFile(__DIR__ . '/skins/slim.png'), 5];
        // Add background to assert, that body still will be black
        yield 'transparent kitty' => [Renderer::assignSkinFromFile(__DIR__ . '/skins/kitty.png'), 5, 255, 0, 0];
    }

    /**
     * @dataProvider degradeCases
     */
    public function testDegrade(Renderer $renderer, bool $overlay): void {
        $this->assertMatchesSnapshot($renderer->degrade($overlay));
    }

    public function degradeCases(): iterable {
        yield 'with overlay' => [Renderer::assignSkinFromFile(__DIR__ . '/skins/modern.png'), true];
        yield 'no overlay' => [Renderer::assignSkinFromFile(__DIR__ . '/skins/modern.png'), false];
    }

    public function testImprove(): void {
        $this->assertMatchesSnapshot(Renderer::assignSkinFromFile(__DIR__ . '/skins/default.png')->improve());
    }

    public function assertMatchesSnapshot($actual): void {
        $this->_assertMatchesSnapshot($actual, $this->driver);
    }

}
