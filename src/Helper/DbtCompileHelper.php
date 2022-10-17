<?php

namespace DbtTransformation\Helper;

use DbtTransformation\Service\DbtService;
use Keboola\Component\UserException;
use SplFileInfo;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;

class DbtCompileHelper
{
    /**
     * @param string $projectDir
     * @return array<int|string, string|false>
     * @throws UserException
     */
    public static function getCompiledSqlFiles(string $projectDir): array
    {
        $compiledDirInfo = new SplFileInfo(
            sprintf('%s/%s/%s', $projectDir, 'target', 'compiled')
        );

        try {
            $finder = new Finder();
            $filePaths = iterator_to_array($finder
                ->files()
                ->in($compiledDirInfo->getPathname())
                ->name('*.sql'));
        } catch (DirectoryNotFoundException $e) {
            throw new UserException('Compiled SQL files not found.');
        }

        $filenames = array_map(fn($sqlFile) => (string) $sqlFile->getFilename(), $filePaths);
        reset($filePaths);

        $contents = array_map(fn($sqlFile) => trim(
            (string) file_get_contents($sqlFile->getPathname())
        ), $filePaths);

        return (array) array_combine($filenames, $contents);
    }
}
