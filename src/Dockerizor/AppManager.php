<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Dockerizor;

use App\Model\Context\App\ComposerAppContext;
use App\Model\Context\App\NodeAppContext;
use App\Model\Context\App\PhpAppContext;
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
        'database_system' => null,
        'root_directory' => null,
        'extra_extensions' => [],
        'extra_packages' => [],
    ];

    /**
     * Create environment context.
     */
    public function getEnvironmentContext(): EnvironmentContext
    {
        $environmentContext = new EnvironmentContext(new DockerComposeFile(), $this->getWorkdir());

        $this->findAppContexts($environmentContext);

        return $environmentContext;
    }

    /**
     * Find app contexts.
     */
    protected function findAppContexts(EnvironmentContext $environmentContext): void
    {
        $filesystem = new Filesystem();
        $workdir = $environmentContext->getWorkdir();

        // Detect composer
        if ($filesystem->exists("{$workdir}/composer.json")) {
            $environmentContext->addAppContext(new ComposerAppContext($workdir));
        }
        // Detect node
        if ($filesystem->exists($nodeFile = "{$workdir}/package.json")) {
            $environmentContext->addAppContext(new NodeAppContext($nodeFile));
        }

        // Detect by file extensions
        if (empty($environmentContext->getAppContexts())) {
            $fileExtensions = $this->countFileExtensions();
            foreach ($fileExtensions as $extension => $count) {
                switch ($extension) {
                    case 'php':
                        if (!$environmentContext->hasAppContext(ComposerAppContext::class)) {
                            $environmentContext->addAppContext(new PhpAppContext());
                        }
                        break;
                    case 'js':
                        if (!$environmentContext->hasAppContext(NodeAppContext::class)) {
                            $environmentContext->addAppContext(new NodeAppContext($nodeFile));
                        }
                        break;
                }
            }
        }
    }

    /**
     * Count file by extensions.
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
