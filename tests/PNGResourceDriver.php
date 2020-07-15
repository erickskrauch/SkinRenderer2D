<?php
namespace ErickSkrauch\SkinRenderer2D\Tests;

use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Driver;
use Spatie\Snapshots\Exceptions\CantBeSerialized;

final class PNGResourceDriver implements Driver {

    public function serialize($data): string {
        if (!is_resource($data)) {
            throw new CantBeSerialized('Only resources can be serialized as a PNG image');
        }

        ob_start();
        imagepng($data);

        return ob_get_clean();
    }

    public function extension(): string {
        return 'png';
    }

    public function match($expected, $actual): void {
        if (is_resource($actual)) {
            $actual = $this->serialize($actual);
        }

        Assert::assertSame($expected, $actual);
    }

}
