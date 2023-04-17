<?php

declare(strict_types=1);

namespace DbtTransformation\Helper;

use Keboola\Component\UserException;
use SplFileInfo;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class DbtCompileHelper
{
    /**
     * @return array<int|string, string|false>
     * @throws UserException
     */
    public static function getCompiledSqlFiles(string $directory): array
    {
        $compiledDirInfo = new SplFileInfo(
            sprintf('%s/%s', $directory, 'compiled')
        );

        try {
            $finder = new Finder();
            $filePaths = iterator_to_array($finder
                ->files()
                ->in($compiledDirInfo->getPathname())
                ->name('*.sql'));
        } catch (DirectoryNotFoundException $e) {
            throw new UserException('Compiled SQL files not found. Run the component with "dbt run" step first.');
        }

        $filenames = array_map(fn($sqlFile) => (string) $sqlFile->getFilename(), $filePaths);
        reset($filePaths);

        $contents = array_map(fn($sqlFile) => trim(
            (string) file_get_contents($sqlFile->getPathname())
        ), $filePaths);

        $combineArray = (array) array_combine($filenames, $contents);
        ksort($combineArray);

        return $combineArray;
    }
}
