<?php

declare(strict_types=1);

namespace DbtTransformation\Tests\Helper;

use DbtTransformation\Helper\YamlParseHelper;
use Generator;
use Keboola\Component\UserException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Exception\ParseException;

class YamlParseHelperTest extends TestCase
{
    /**
     * @dataProvider sanitizeParseErrorMessageProvider
     */
    public function testSanitizeParseErrorMessage(ParseException $exception, string $expectedMessage): void
    {
        self::assertSame($expectedMessage, YamlParseHelper::sanitizeParseErrorMessage($exception));
    }

    public function sanitizeParseErrorMessageProvider(): Generator
    {
        yield 'message with file and snippet' => [
            'exception' => new ParseException(
                'Duplicate key "dev" detected.',
                6,
                '        dev:',
                '/data/profiles.yml',
            ),
            'expectedMessage' => 'Duplicate key "dev" detected at line 6.',
        ];

        yield 'message without snippet' => [
            'exception' => new ParseException('Unable to parse.', 3, null, '/data/profiles.yml'),
            'expectedMessage' => 'Unable to parse at line 3.',
        ];

        yield 'message without file or line' => [
            'exception' => new ParseException('Unable to parse.'),
            'expectedMessage' => 'Unable to parse.',
        ];
    }

    /**
     * @dataProvider containsJinjaProvider
     */
    public function testContainsJinja(ParseException $exception, bool $expected): void
    {
        self::assertSame($expected, YamlParseHelper::containsJinja($exception));
    }

    public function containsJinjaProvider(): Generator
    {
        yield 'double curly expression in snippet' => [
            'exception' => new ParseException('Malformed inline YAML string.', 1, "key: {{ env_var('X') }}"),
            'expected' => true,
        ];

        yield 'statement block in snippet' => [
            'exception' => new ParseException('Malformed inline YAML string.', 1, '{% if true %}key: 1{% endif %}'),
            'expected' => true,
        ];

        yield 'comment block in snippet' => [
            'exception' => new ParseException('Malformed inline YAML string.', 1, 'key: 1 {# comment #}'),
            'expected' => true,
        ];

        yield 'plain yaml snippet' => [
            'exception' => new ParseException('Duplicate key "dev" detected.', 7, '    type: snowflake'),
            'expected' => false,
        ];

        yield 'no snippet available' => [
            'exception' => new ParseException('Unable to parse.'),
            'expected' => false,
        ];
    }

    public function testToUserExceptionDoesNotLeakFileContentOrPath(): void
    {
        $exception = new ParseException(
            'Duplicate key "dev" detected.',
            6,
            '        password: super-secret',
            '/data/profiles.yml',
        );

        $userException = YamlParseHelper::toUserException($exception, 'profiles.yml');

        self::assertInstanceOf(UserException::class, $userException);
        self::assertStringContainsString('Invalid YAML in "profiles.yml"', $userException->getMessage());
        self::assertStringContainsString('Duplicate key "dev" detected at line 6', $userException->getMessage());
        self::assertStringNotContainsString('super-secret', $userException->getMessage());
        self::assertStringNotContainsString('/data/profiles.yml', $userException->getMessage());
    }
}
