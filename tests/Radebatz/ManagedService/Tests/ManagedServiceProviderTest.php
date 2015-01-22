<?php

/*
* This file is part of the ManagedService service provider.
*
* (c) Martin Rademacher <mano@radebatz.net>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Radebatz\ManagedService\Tests;

use stdClass;
use ReflectionClass;
use PHPUnit_Framework_TestCase;
use Silex\Application;
use Radebatz\ManagedService\ManagedServiceProvider;

/**
 * ManagedServiceProvider test cases.
 *
 * @author Martin Rademacher <mano@radebatz.net>
 */
class ManagedServiceProviderTests extends PHPUnit_Framework_TestCase
{

    public function testOptionsInitializer()
    {
        $app = new Application();
        $provider1 = new ManagedServiceProvider('pre1', 'stdClass');

        // check initialized flag: false
        $rc = new ReflectionClass($provider1);
        $initialized = $rc->getProperty('initialized');
        $initialized->setAccessible (true);
        $this->assertFalse($initialized->getValue($provider1));

        $app->register($provider1);

        $this->assertEquals($app['pre1.default_options'], array());

        // check initialized flag: false
        $rc = new ReflectionClass($provider1);
        $initialized = $rc->getProperty('initialized');
        $initialized->setAccessible (true);
        $this->assertFalse($initialized->getValue($provider1));

        // access any service
        $app['pre1'];

        // check initialized flag: true
        $rc = new ReflectionClass($provider1);
        $initialized = $rc->getProperty('initialized');
        $initialized->setAccessible (true);
        $this->assertTrue($initialized->getValue($provider1));

        // set some defaults
        $defaultOptions = array('foo' => 'bar');
        $provider2 = new ManagedServiceProvider('pre2', 'stdClass');
        $app->register($provider2, array('pre2.default_options' => $defaultOptions));

        $this->assertEquals($defaultOptions, array('foo' => 'bar'));
    }

    public function testServiceClassName()
    {
        $app = new Application();
        $app->register(new ManagedServiceProvider('pre', 'stdClass'));

        // default
        $this->assertNotNull($app['pre']);
        $this->assertTrue($app['pre'] instanceof stdClass);
        $this->assertTrue($app['pres']['default'] instanceof stdClass);
    }

    public function testServiceClosure()
    {
        $app = new Application();
        // add some options to make the closure work...
        $defaultOptions = array('foo' => 'bar');
        $app->register(new ManagedServiceProvider('pre', function($name, $options, $mms) { return json_decode(json_encode($options)); }), array('pre.default_options' => $defaultOptions));

        // default
        $this->assertNotNull($app['pre']);
        $this->assertEquals('bar', $app['pre']->foo);
        $this->assertTrue($app['pre'] instanceof stdClass);
        $this->assertTrue($app['pres']['default'] instanceof stdClass);
    }

    public function testMultiConfig()
    {
        $app = new Application();
        $app->register(new ManagedServiceProvider('pre', function($name, $options, $mms) { return json_decode(json_encode($options)); }));
        $app['pres.options'] = array(
            'reader' => array(
                'name' => 'reader'
            ),
            'writer' => array(
                'name' => 'writer'
            ),
        );

        $this->assertNotNull($app['pre']);
        $this->assertTrue($app['pres']['reader'] instanceof stdClass);
        $this->assertEquals('reader', $app['pres']['reader']->name);
        $this->assertTrue($app['pres']['writer'] instanceof stdClass);
        $this->assertEquals('writer', $app['pres']['writer']->name);
    }

}
