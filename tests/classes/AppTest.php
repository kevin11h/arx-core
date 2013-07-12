<?php

use Mockery as m;
use Arx\classes\Request;
use Arx\classes\App;

class AppTest extends PHPUnit_Framework_TestCase {

    public function tearDown()
    {
        m::close();
    }


    public function testEnvironmentDetection()
    {
        $app = m::mock('Arx\classes\App[runningInConsole]');
        $app['request'] = m::mock('Symfony\Component\HttpFoundation\Request');
        $app['request']->shouldReceive('getHost')->andReturn('foo');
        $app['request']->server = m::mock('StdClass');
        $app['request']->server->shouldReceive('get')->once()->with('argv')->andReturn(array());
        $app->shouldReceive('runningInConsole')->once()->andReturn(false);
        $app->detectEnvironment(array(
            'local'   => array('localhost')
        ));
        $this->assertEquals('production', $app['env']);

        $app = m::mock('Arx\classes\App[runningInConsole]');
        $app['request'] = m::mock('Symfony\Component\HttpFoundation\Request');
        $app['request']->shouldReceive('getHost')->andReturn('localhost');
        $app['request']->server = m::mock('StdClass');
        $app['request']->server->shouldReceive('get')->once()->with('argv')->andReturn(array());
        $app->shouldReceive('runningInConsole')->once()->andReturn(false);
        $app->detectEnvironment(array(
            'local'   => array('localhost')
        ));
        $this->assertEquals('local', $app['env']);

        $app = m::mock('Arx\classes\App[runningInConsole]');
        $app['request'] = m::mock('Symfony\Component\HttpFoundation\Request');
        $app['request']->shouldReceive('getHost')->andReturn('localhost');
        $app['request']->server = m::mock('StdClass');
        $app['request']->server->shouldReceive('get')->once()->with('argv')->andReturn(array());
        $app->shouldReceive('runningInConsole')->once()->andReturn(false);
        $app->detectEnvironment(array(
            'local'   => array('local*')
        ));
        $this->assertEquals('local', $app['env']);

        $app = m::mock('Arx\classes\App[runningInConsole]');
        $app['request'] = m::mock('Symfony\Component\HttpFoundation\Request');
        $app['request']->shouldReceive('getHost')->andReturn('localhost');
        $app['request']->server = m::mock('StdClass');
        $app['request']->server->shouldReceive('get')->once()->with('argv')->andReturn(array());
        $app->shouldReceive('runningInConsole')->once()->andReturn(false);
        $host = gethostname();
        $app->detectEnvironment(array(
            'local'   => array($host)
        ));
        $this->assertEquals('local', $app['env']);
    }

    /**
     * @todo fix this
     */
//    public function testClosureCanBeUsedForCustomEnvironmentDetection()
//    {
//        $app = m::mock('Arx\classes\App[runningInConsole]');
//        $app['request'] = m::mock('Symfony\Component\HttpFoundation\Request');
//        $app['request']->shouldReceive('getHost')->andReturn('foo');
//        $app['request']->server = m::mock('StdClass');
//        $app['request']->server->shouldReceive('get')->once()->with('argv')->andReturn(array());
//        $app->shouldReceive('runningInConsole')->once()->andReturn(false);
//        $app->detectEnvironment(function() { return 'foobar'; });
//        $this->assertEquals('foobar', $app['env']);
//    }

    /**
     * @todo fix environnement
     */
//    public function testConsoleEnvironmentDetection()
//    {
//        $app = new App;
//        $app['request'] = m::mock('Symfony\Component\HttpFoundation\Request');
//        $app['request']->shouldReceive('getHost')->andReturn('foo');
//        $app['request']->server = m::mock('StdClass');
//        $app['request']->server->shouldReceive('get')->once()->with('argv')->andReturn(array('--env=local'));
//        $app->detectEnvironment(array(
//            'local'   => array('foobar')
//        ));
//        $this->assertEquals('local', $app['env']);
//    }


    public function testPrepareRequestInjectsSession()
    {
        $app = new App;
        $request = Request::create('/', 'GET');
        $app['session'] = m::mock('Illuminate\Session\Store');
        $app->prepareRequest($request);
        $this->assertEquals($app['session'], $request->getSessionStore());
    }


    public function testSetLocaleSetsLocaleAndFiresLocaleChangedEvent()
    {
        $app = new App;
        $app['config'] = $config = m::mock('StdClass');
        $config->shouldReceive('set')->once()->with('app.locale', 'foo');
        $app['translator'] = $trans = m::mock('StdClass');
        $trans->shouldReceive('setLocale')->once()->with('foo');
        $app['events'] = $events = m::mock('StdClass');
        $events->shouldReceive('fire')->once()->with('locale.changed', array('foo'));

        $app->setLocale('foo');
    }


    public function testServiceProvidersAreCorrectlyRegistered()
    {
        $provider = m::mock('Illuminate\Support\ServiceProvider');
        $class = get_class($provider);
        $provider->shouldReceive('register')->once();
        $app = new App;
        $app->register($provider);

        $this->assertTrue(in_array($class, $app->getLoadedProviders()));
    }

}

class AppCustomExceptionHandlerStub extends Arx\classes\App {

    public function prepareResponse($value)
    {
        $response = m::mock('Symfony\Component\HttpFoundation\Response');
        $response->shouldReceive('send')->once();
        return $response;
    }

    protected function setExceptionHandler(Closure $handler) { return $handler; }

}

class AppKernelExceptionHandlerStub extends Arx\classes\App {

    protected function setExceptionHandler(Closure $handler) { return $handler; }

}