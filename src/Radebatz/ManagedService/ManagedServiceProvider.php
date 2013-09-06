<?php

/*
* This file is part of the ManagedService service provider.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ManagedService;

use Pimple;
use Silex\Application;
use Silex\ServiceProviderInterface;

/**
 * Manage a single service with multiple configurations.
 *
 * The prefix for multiple configurations will be the given <code>$prefix</code> with a suffix of <em>s</em> added.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class ManagedServiceProvider implements ServiceProviderInterface
{
    protected $initialized;
    protected $prefix;
    protected $service;

    /**
     * Create instance for the given settings.
     *
     * @param string $prefix The (singular) config prefix for this service.
     * @param mixed $service Either a service class name or a <code>callable</em>. In case of class name it is assumed that
     *  the constructor accepts a options array as single parameter. If a <code>callable</code> is given it will be called with
     *  the configuration name, options array and service container as parameters and a service instance is expected as return value.
     */
    public function __construct($prefix, $service)
    {
        $this->prefix = $prefix;
        $this->service = $service;
        $this->initialized = false;
    }

    /**
     * {@inheritDoc}
     */
    public function register(Application $app)
    {
        $prefix = $this->prefix;
        $service = $this->service;
        $initialized = &$this->initialized;

        $app[$prefix.'.default_options'] = isset($app[$prefix.'.default_options']) ? $app[$prefix.'.default_options'] : array();

        $app[$prefix.'s.options.initializer'] = $app->protect(function () use ($app, &$initialized, $prefix) {
            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($app[$prefix.'s.options'])) {
                $app[$prefix.'s.options'] = array('default' => isset($app[$prefix.'.options']) ? $app[$prefix.'.options'] : array());
            }

            $tmp = $app[$prefix.'s.options'];
            foreach ($tmp as $name => &$options) {
                $options = array_replace($app[$prefix.'.default_options'], $options);

                if (!isset($app[$prefix.'s.default'])) {
                    $app[$prefix.'s.default'] = $name;
                }
            }
            $app[$prefix.'s.options'] = $tmp;
        });

        $app[$prefix.'s'] = $app->share(function ($app) use ($prefix, $service) {
            $app[$prefix.'s.options.initializer']();
            $mms = new Pimple();
            $isCallable = is_callable($service);
            foreach ($app[$prefix.'s.options'] as $name => $options) {
                $mms[$name] = $isCallable ? call_user_func($service, $name, $options, $mms) : $mms[$name] = new $service($options);
            }

            return $mms;
        });

        // shortcut for default/first service
        $app[$prefix] = $app->share(function ($app) use ($prefix) {
            $mms = $app[$prefix.'s'];

            return $mms[$app[$prefix.'s.default']];
        });
        // shortcut for default/first config
        $app[$prefix.'.config'] = $app->share(function ($app) use ($prefix) {
            $mms = $app[$prefix.'s.config'];

            return $mms[$app[$prefix.'s.default']];
        });
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        // nothing
    }

}
