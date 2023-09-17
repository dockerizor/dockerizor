<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model;

enum Mode: string
{
    case phar = 'phar';
    case docker = 'docker';
    case app = 'app';
    case unknown = 'unknown';
}
