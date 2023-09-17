<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Dockerizor;

use App\Model\Context\App\ComposerAppContext;
use App\Model\Context\App\NodeAppContext;
use App\Model\Context\App\PhpAppContext;
use App\Model\Context\App\PythonAppContext;
use App\Model\Context\EnvironmentContext;
use App\Model\Docker\DockerComposeFile;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class AppManager extends AbstractManager
{
    protected ?string $workdir = null;
    protected string $configFilename = 'dockerizor.json';
    protected array $config = [];
    protected array $defaultConfig = [
        'app_name' => null,
        'domain' => null,
        'port' => null,
        'database_url' => null,
        'root_directory' => null,
        'extra_extensions' => [],
        'extra_packages' => [],
    ];

    /**
     * Create environment context.
     * 
     * @return EnvironmentContext
     */
    public function getEnvironmentContext(): EnvironmentContext
    {
        $environmentContext = new EnvironmentContext(new DockerComposeFile(), $this->getWorkdir());

        $this->findAppContexts($environmentContext);

        return $environmentContext;
    }

    /**
     * Find app contexts.
     * 
     * @param EnvironmentContext $environmentContext
     * 
     * @return void
     */
    protected function findAppContexts(EnvironmentContext $environmentContext): void
    {
        $filesystem = new Filesystem();
        $workdir = $environmentContext->getWorkdir();

        // Detect composer
        if ($filesystem->exists($composerFile = "{$workdir}/composer.json")) {
            $environmentContext->addAppContext(new ComposerAppContext($workdir));
        }
        // Detect node
        if ($filesystem->exists($nodeFile = "{$workdir}/package.json")) {
            $environmentContext->addAppContext(new NodeAppContext($nodeFile));
        }

        // Detect by file extensions
        $fileExtensions = $this->countFileExtensions();
        foreach ($fileExtensions as $extension => $count) {
            switch ($extension) {
                case 'php':
                    if (!$environmentContext->hasAppContext(ComposerAppContext::class)) {
                        $contexts[] = new PhpAppContext($composerFile);
                    }
                    break;
                case 'js':
                    $contexts[] = new NodeAppContext($nodeFile);
                    break;
            }
        }
    }

    /**
     * Count file by extensions
     * 
     * @return array
     */
    protected function countFileExtensions(): array
    {
        $workdir = $this->getWorkdir();
        $finder = new Finder();
        $finder->files()->ignoreUnreadableDirs()->in($workdir);

        $extensions = [];
        foreach ($finder as $file) {
            $extension = $file->getExtension();
            if (!isset($extensions[$extension])) {
                $extensions[$extension] = 0;
            }
            ++$extensions[$extension];
        }

        arsort($extensions);

        return $extensions;
    }
}
