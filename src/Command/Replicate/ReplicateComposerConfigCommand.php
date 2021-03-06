<?php

declare(strict_types=1);

namespace Yiisoft\YiiDevTool\Command\Replicate;

use Yiisoft\YiiDevTool\Component\Composer\Config\ComposerConfig;
use Yiisoft\YiiDevTool\Component\Composer\Config\ComposerConfigMerger;
use Yiisoft\YiiDevTool\Component\Console\PackageCommand;
use Yiisoft\YiiDevTool\Component\Package\Package;

class ReplicateComposerConfigCommand extends PackageCommand
{
    protected function configure()
    {
        $this
            ->setName('replicate/composer-config')
            ->setDescription('Merge <fg=blue;options=bold>config/replicate/composer.json</> into <fg=blue;options=bold>composer.json</> of each package');

        $this->addPackageArgument();
    }

    protected function getMessageWhenNothingHasBeenOutput(): ?string
    {
        return '<success>✔ Done</success>';
    }

    protected function processPackage(Package $package): void
    {
        $io = $this->getIO();
        $io->preparePackageHeader($package, "Merging <file>config/replicate/composer.json</file> to package {package}");

        $targetPath = "{$package->getPath()}/composer.json";
        if (!file_exists($targetPath)) {
            $io->warning([
                "No <file>composer.json</file> in package {$package->getId()}.",
                "Replication of composer config skipped.",
            ]);

            return;
        }

        $merger = new ComposerConfigMerger();

        $mergedConfig = $merger->merge(
            ComposerConfig::createByFilePath($targetPath),
            ComposerConfig::createByFilePath(__DIR__ . '/../../../config/replicate/composer.json'),
        );

        $mergedConfig->writeToFile($targetPath);

        $io->done();
    }
}
