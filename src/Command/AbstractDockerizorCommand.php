<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Command;

use Phar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

abstract class AbstractDockerizorCommand extends Command
{
    protected $parameterBag;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->parameterBag = $parameterBag;

        parent::__construct();
    }

    protected function isPhar()
    {
        return '' !== \Phar::running();
    }

    protected function getWorkdir()
    {
        // Phar
        if ($this->isPhar()) {
            return getcwd();
        }

        // Docker
        if ('/dockerizor' === getcwd()) {
            return '/dockerizor/app';
        }

        // Test
        if ($this->parameterBag->get('kernel.project_dir') === getcwd()) {
            return $this->parameterBag->get('kernel.project_dir').'/app';
        }

        return getcwd();
    }
}
