<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model;

enum Mode: string
{
    case phar = 'phar';
    case docker = 'docker';
    case app = 'app';
    case unknown = 'unknown';
}
