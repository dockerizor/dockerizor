<?php

/*
 * This file is part of the Dockerisor package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Model\Context\App;

class NodeAppContext extends AppContext implements AppContextInterface
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
     */
    public function getDevDependencies(): array
    {
        return $this->data['devDependencies'] ?? [];
    }
}
