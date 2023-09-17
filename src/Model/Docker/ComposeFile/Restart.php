<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Docker\ComposeFile;

enum Restart: string
{
    case no = 'no';
    case always = 'always';
    case on_failure = 'on-failure';
    case unless_stopped = 'unless-stopped';
}
