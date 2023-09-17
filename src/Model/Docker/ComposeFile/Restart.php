<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Docker\ComposeFile;

enum Restart: string
{
    case no = 'no';
    case always = 'always';
    case on_failure = 'on-failure';
    case unless_stopped = 'unless-stopped';
}
