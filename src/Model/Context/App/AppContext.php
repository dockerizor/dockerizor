<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\App;

use App\Model\Context\Build\AppBuildContext;

class AppContext
{
    protected ?AppBuildContext $buildContext = null;

    /**
     * Set app build context.
     */
    public function setAppBuildContext(AppBuildContext $buildContext): self
    {
        $this->buildContext = $buildContext;

        return $this;
    }

    /**
     * Get app build context.
     */
    public function getAppBuildContext(): ?AppBuildContext
    {
        return $this->buildContext;
    }
}
