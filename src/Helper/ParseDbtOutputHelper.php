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
            $message = json_decode($messageJson, true);
            if ($message === null || is_numeric(array_key_first($message))) {
                return yield $output;
            }
            if ($message['level'] === $level) {
                yield preg_replace('#\\x1b[[][^A-Za-z]*[A-Za-z]#', '', $message['msg']);
            }
        }
    }
}
