<?php

declare(strict_types=1);

namespace DbtTransformation\Helper;

use Keboola\Component\UserException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class YamlParseHelper
{
    /**
     * @throws UserException
     */
    public static function parseUserYamlFile(string $path, string $fileLabel): mixed
    {
        try {
            return Yaml::parseFile($path);
        } catch (ParseException $e) {
            throw self::toUserException($e, $fileLabel);
        }
    }

    public static function toUserException(ParseException $e, string $fileLabel): UserException
    {
        return new UserException(sprintf(
            'Invalid YAML in "%s": %s Fix the file in your dbt project and run the job again.',
            $fileLabel,
            self::sanitizeParseErrorMessage($e),
        ), 0, $e);
    }

    public static function containsJinja(string $content): bool
    {
        return str_contains($content, '{{')
            || str_contains($content, '{%')
            || str_contains($content, '{#');
    }

    /**
     * Strips the absolute file path and the file-content snippet that Symfony appends to ParseException
     * messages (see ParseException::updateRepr()) — the snippet quotes the raw failing line, which may
     * contain secrets, and must never reach job logs or user-facing errors. Keeps the reason and line number.
     */
    public static function sanitizeParseErrorMessage(ParseException $e): string
    {
        $message = $e->getMessage();

        $parsedFile = (string) $e->getParsedFile();
        if ($parsedFile !== '') {
            $message = str_replace(
                sprintf(' in %s', (string) json_encode($parsedFile, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)),
                '',
                $message,
            );
        }

        $snippet = (string) $e->getSnippet();
        if ($snippet !== '') {
            $message = str_replace(sprintf(' (near "%s")', $snippet), '', $message);
        }

        return $message;
    }
}
