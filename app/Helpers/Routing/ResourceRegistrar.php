<?php

namespace App\Helpers\Routing;

use Illuminate\Routing\ResourceRegistrar as OriginalRegistrar;

class ResourceRegistrar extends OriginalRegistrar
{
    /**
     * The default actions for a resourceful controller.
     *
     * @var string[]
     */
    protected $resourceDefaults = ['index', 'create', 'store', 'import', 'show', 'edit', 'update', 'destroy', 'export'];

    /**
     * Add the export method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceExport($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/export';

        $action = $this->getResourceAction($name, $controller, 'export', $options);

        return $this->router->post($uri, $action);
    }

    /**
     * Add the import method for a resourceful route.
     *
     * @param  string  $name
     * @param  string  $base
     * @param  string  $controller
     * @param  array  $options
     * @return \Illuminate\Routing\Route
     */
    protected function addResourceImport($name, $base, $controller, $options)
    {
        $uri = $this->getResourceUri($name).'/import';

        $action = $this->getResourceAction($name, $controller, 'import', $options);

        return $this->router->match(['get', 'post'], $uri, $action);
    }
}
