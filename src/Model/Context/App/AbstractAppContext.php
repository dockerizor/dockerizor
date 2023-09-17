<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\App;

use App\Model\Context\Build\AppBuildContext;

abstract class AbstractAppContext
{
    protected AppBuildContext $buildContext;

    /** 
     * Set app build context.
     * 
     * @param AppBuildContext $buildContext
     * 
     * @return self
     */
    public function setAppBuildContext(AppBuildContext $buildContext): self
    {
        $this->buildContext = $buildContext;

        return $this;
    }

    /**
     * Get app build context.
     * 
     * @return AppBuildContext
     */
    public function getAppBuildContext(): AppBuildContext
    {
        return $this->buildContext;
    }
}
