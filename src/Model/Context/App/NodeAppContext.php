<?php

/*
 * This file is part of the Dockerisor package.
 *
 * @license    https://opensource.org/licenses/MIT MIT License
 */

namespace App\Model\Context\App;

class NodeAppContext extends AbstractAppContext implements AppContextInterface
{
    protected string $filename;
    protected array $data;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $this->data = json_decode(file_get_contents($filename), true);
    }

    /**
     * Get dependencies.
     * 
     * @return array
     */
    public function getDevDependencies(): array
    {
        return $this->data['devDependencies'] ?? [];
    }
}
