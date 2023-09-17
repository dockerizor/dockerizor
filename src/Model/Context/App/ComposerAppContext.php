<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\App;

use App\Model\Context\Framework\Common;
use App\Model\Context\Framework\FrameworkInterface;
use App\Model\Context\Framework\Symfony;
use App\Model\Enum\Framework;

class ComposerAppContext extends AbstractAppContext implements AppContextInterface
{
    protected string $composerFile;
    protected array $composerFileData;
    protected string $lockFile;
    protected array $lockFileData;
    protected ?FrameworkInterface $framework = null;

    public function __construct(string $workdir)
    {
        // Load composer.json
        $this->composerFile = "{$workdir}/composer.json";
        $this->composerFileData = json_decode(file_get_contents($this->composerFile), true);

        // Load composer.lock if exists
        $this->lockFile = "{$workdir}/composer.lock";
        if (file_exists($this->lockFile)) {
            $this->lockFileData = json_decode(file_get_contents($this->lockFile), true);
        }

        $this->resolveFramework();
    }

    /**
     * Resolve framework.
     */
    protected function resolveFramework(): void
    {
        foreach ($this->lockFileData['packages'] ?? $this->composerFileData['require'] as $key => $data) {
            $name = $data['name'] ?? $key;
            $version = $data['version'] ?? $data;

            switch ($name) {
                case 'symfony/framework-bundle':
                    $this->framework = new Symfony($version, '/var/www/html/public');
                    break;
                case 'laravel/framework':
                    $this->framework = new Common(Framework::LARAVEL->value, $version, '/var/www/html/public');
                    break;
                case 'cakephp/cakephp':
                    $this->framework = new Common(Framework::CAKEPHP->value, $version, '/var/www/html/webroot');
                    break;
                case 'yiisoft/yii2':
                    $this->framework = new Common(Framework::YII2->value, $version, '/var/www/html/web');
                    break;
                case 'zendframework/zendframework':
                    $this->framework = new Common(Framework::ZEND->value, $version, '/var/www/html/public');
                    break;
                case 'codeigniter/framework':
                    $this->framework = new Common(Framework::CODEIGNITER->value, $version, '/var/www/html/public');
                    break;
                case 'slim/slim':
                    $this->framework = new Common(Framework::SLIM->value, $version, '/var/www/html/public');
                    break;
                case 'drupal/core':
                    $this->framework = new Common(Framework::DRUPAL->value, $version, '/var/www/html/web');
                    break;
                case 'typo3/cms-core':
                    $this->framework = new Common(Framework::TYPO3->value, $version, '/var/www/html/public');
                    break;
                case 'magento/magento2-base':
                    $this->framework = new Common(Framework::MAGENTO->value, $version, '/var/www/html/pub');
                    break;
                case 'joomla/joomla-cms':
                    $this->framework = new Common(Framework::JOOMLA->value, $version, '/var/www/html/public');
                    break;
                case 'wordpress/wordpress':
                    $this->framework = new Common(Framework::WORDPRESS->value, $version, '/var/www/html/public');
                    break;
                case 'prestashop/prestashop':
                    $this->framework = new Common(Framework::PRESTASHOP->value, $version, '/var/www/html/public');
                    break;
                case 'opencart/opencart':
                    $this->framework = new Common(Framework::OPENCART->value, $version, '/var/www/html/public');
                    break;
                case 'fuel/fuel':
                    $this->framework = new Common(Framework::FUEL->value, $version, '/var/www/html/public');
                    break;
                case 'phalcon/cphalcon':
                    $this->framework = new Common(Framework::PHALCON->value, $version, '/var/www/html/public');
                    break;
                case 'laminas/laminas-mvc':
                    $this->framework = new Common(Framework::LAMINAS->value, $version, '/var/www/html/public');
                    break;
                case 'lumen/lumen':
                    $this->framework = new Common(Framework::LUMEN->value, $version, '/var/www/html/public');
                    break;
                case 'slim/slim':
                    $this->framework = new Common(Framework::SLIM->value, $version, '/var/www/html/public');
                    break;
            }
        }
    }

    /**
     * Get framework.
     */
    public function getFramework(): ?FrameworkInterface
    {
        return $this->framework;
    }
}
