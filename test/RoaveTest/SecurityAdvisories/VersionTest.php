<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

declare(strict_types=1);

namespace RoaveTest\SecurityAdvisories;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Roave\SecurityAdvisories\Version;
use function array_combine;
use function array_map;
use function assert;
use function is_array;

/**
 * Tests for {@see \Roave\SecurityAdvisories\Version}
 *
 * @covers \Roave\SecurityAdvisories\Version
 */
final class VersionTest extends TestCase
{
    /**
     * @dataProvider invalidVersionStringsProvider
     */
    public function testVersionWillNotAllowInvalidFormats(string $versionString) : void
    {
        $this->expectException(InvalidArgumentException::class);

        Version::fromString($versionString);
    }

    /**
     * @dataProvider validVersionStringProvider
     */
    public function testGetVersionWithValidVersion(string $versionString) : void
    {
        self::assertRegExp('/([0-9]*)(\\.[1-9][0-9]*)*/', Version::fromString($versionString)->getVersion());
    }

    /**
     * @dataProvider validVersionStringProvider
     */
    public function testVersionNumbersAreNormalized(string $versionString) : void
    {
        self::assertNotRegExp('/(\\.[0]+)+$/', Version::fromString($versionString)->getVersion());
    }

    /**
     * @dataProvider greaterThanComparisonVersionsProvider
     */
    public function testGreaterThanVersionWith(
        string $version1String,
        string $version2String,
        bool $v1GreaterThanV2,
        bool $v2GreaterThanV1
    ) : void {
        $version1 = Version::fromString($version1String);
        $version2 = Version::fromString($version2String);

        self::assertSame($v1GreaterThanV2, $version1->isGreaterThan($version2));
        self::assertSame($v2GreaterThanV1, $version2->isGreaterThan($version1));
    }

    /**
     * @dataProvider greaterOrEqualThanComparisonVersionsProvider
     */
    public function testGreaterOrEqualThanVersionWith(
        string $version1String,
        string $version2String,
        bool $v1GreaterOrEqualThanV2,
        bool $v2GreaterOrEqualThanV1
    ) : void {
        $version1 = Version::fromString($version1String);
        $version2 = Version::fromString($version2String);

        self::assertSame($v1GreaterOrEqualThanV2, $version1->isGreaterOrEqualThan($version2));
        self::assertSame($v2GreaterOrEqualThanV1, $version2->isGreaterOrEqualThan($version1));
    }

    /**
     * @dataProvider equivalentVersionProvider
     */
    public function testVersionEquivalence(string $version1String, string $version2String) : void
    {
        $version1 = Version::fromString($version1String);
        $version2 = Version::fromString($version2String);

        self::assertEquals($version1, $version2);
        self::assertTrue($version1->equalTo($version2));
        self::assertTrue($version2->equalTo($version1));
    }

    /**
     * @dataProvider nonEquivalentVersionProvider
     */
    public function testVersionNonEquivalence(string $version1String, string $version2String) : void
    {
        $version1 = Version::fromString($version1String);
        $version2 = Version::fromString($version2String);

        self::assertNotEquals($version1, $version2);
        self::assertFalse($version1->equalTo($version2));
        self::assertFalse($version2->equalTo($version1));
    }

    /**
     * @return string[][]
     */
    public function validVersionStringProvider() : array
    {
        return [
            ['0'],
            ['1'],
            ['12345'],
            ['12345.00'],
            ['0.1.2.3.4'],
            ['1.2.3.4'],
            ['1.2.3.4.5.6.7.8.9.10'],
            ['12345.12345.12345.12345.0'],
        ];
    }

    /**
     * @return string[][]|bool[][]
     */
    public function greaterThanComparisonVersionsProvider() : array
    {
        $versions = [
            ['0', '0', false, false],
            ['1', '1', false, false],
            ['3', '3', false, false],
            ['100', '99', true, false],
            ['1', '0', true, false],
            ['1.1', '1.1', false, false],
            ['1.10', '1.1', true, false],
            ['1.100', '1.100', false, false],
            ['1.2', '1.100', false, true],
            ['1.1', '1.1.0', false, false],
            ['1.1', '1.1.0.0', false, false],
            ['1.1', '1.1.0.0.1', false, true],
            ['1.0.0.0.0.0.2', '1.0.0.0.0.2', false, true],
            ['1.0.12', '1.0.11', true, false],
        ];

        $indexedEntries = array_combine(
            array_map(
                static function (array $versionData) {
                    return $versionData[0] . ' > ' . $versionData[1];
                },
                $versions
            ),
            $versions
        );

        assert(is_array($indexedEntries));

        return $indexedEntries;
    }

    /**
     * @return string[][]|bool[][]
     */
    public function greaterOrEqualThanComparisonVersionsProvider() : array
    {
        $versions = [
            ['0', '0', true, true],
            ['0.0', '0', true, true],
            ['0.0.0', '0', true, true],
            ['0.0.0.1', '0', true, false],
            ['100', '99', true, false],
            ['1', '0', true, false],
            ['1.1', '1.1', true, true],
            ['1.10', '1.1', true, false],
            ['1.10', '1.10', true, true],
            ['1.100', '1.100', true, true],
            ['1.2', '1.100', false, true],
            ['1.1', '1.1.0', true, true],
            ['1.1', '1.1.0.0', true, true],
            ['1.1', '1.1.0.0.1', false, true],
            ['1.0.0.0.0.0.2', '1.0.0.0.0.2', false, true],
            ['1.0.12', '1.0.11', true, false],
        ];

        $indexedEntries = array_combine(
            array_map(
                static function (array $versionData) {
                    return $versionData[0] . ' >= ' . $versionData[1];
                },
                $versions
            ),
            $versions
        );

        assert(is_array($indexedEntries));

        return $indexedEntries;
    }

    /**
     * @return string[][]
     */
    public function invalidVersionStringsProvider() : array
    {
        return [
            [''],
            ['12.a'],
            ['12a3'],
            ['alpha'],
            ['beta'],
            ['1-a'],
            ['1.2.a'],
            ['.1'],
        ];
    }

    /**
     * @return string[][]
     */
    public function equivalentVersionProvider() : array
    {
        return [
            ['0', '0.0'],
            ['1', '1.0'],
            ['1', '1.0.0'],
            ['1.0.0.0', '1.0.0'],
            ['2.0.1.0', '2.0.1'],
            ['2.0.1.0.0.0', '2.0.1'],
        ];
    }

    /**
     * @return string[][]
     */
    public function nonEquivalentVersionProvider() : array
    {
        return [
            ['0.1', '0.0'],
            ['1.0.1', '1.0'],
            ['1', '1.0.2'],
            ['1.0.0.1', '1.0.0'],
            ['2.0.1.1', '2.0.1'],
            ['2.0.1.0.0.0', '2.0.2'],
        ];
    }
}
