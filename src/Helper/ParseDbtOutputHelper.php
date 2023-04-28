<?php

declare(strict_types=1);

namespace DbtTransformation\Helper;

use Generator;

class ParseDbtOutputHelper
{

    /**
     * @return \Generator<string>
     */
    public static function getMessagesFromOutput(string $output, string $level = 'info'): Generator
    {
        $matches = preg_match_all('~\{(?:[^{}]|(?R))*}~', $output, $messages);
        if (!$matches) {
            return yield $output;
        }
        foreach (reset($messages) as $messageJson) {
            $message = (array) json_decode($messageJson, true);
            if (empty($message) || is_numeric(array_key_first($message))) {
                return yield $output;
            }
            if (isset($message['level'])) { //dbt-core < 1.4
                if ($message['level'] === $level) {
                    yield $message['msg'];
                }
            } else { //dbt-core >= 1.4
                if (isset($message['info']['level']) && $message['info']['level'] === $level) {
                    /** @var array<string, array<string, string>> $message */
                    yield $message['info']['msg'];
                }
            }
        }
    }
}
