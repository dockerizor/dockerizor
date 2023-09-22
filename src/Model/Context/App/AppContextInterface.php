<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\App;

use App\Model\Context\Build\AppBuildContext;

interface AppContextInterface
{
    public function setAppBuildContext(AppBuildContext $buildContext): AppContext;

    public function getAppBuildContext(): ?AppBuildContext;
}
