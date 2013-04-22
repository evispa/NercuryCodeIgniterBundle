What it is
==========

Integrates CodeIgniter as a bundle in Symfony2.

Usage
=====

Install bundle over composer, and load it in AppKernel::

    $bundles = array(
        ...
        new Nercury\CodeIgniterBundle\NercuryCodeIgniterBundle(),
        ...
    );

In case you are still using Symfony 2.0, do not forget to autoload the bundle in autoload.php::

    'Nercury' => __DIR__.'/../vendor/nercury/code-igniter-bundle',

In config.yml, specify paths to CodeIgniter directories::

    nercury_code_igniter:
        application_path: %kernel.root_dir%/../CodeIgniter_210/application
        system_path: %kernel.root_dir%/../CodeIgniter_210/system

Bundle uses a bit of code from CodeIgniter 2.1.0, so it should work the best with 2.1.0 version.
Bundle automatically tries to detect code igniter controllers based on current URL.
To disable this add the following line to config.yml::

    nercury_code_igniter:
        ...
        detect_controllers: false

To get CI response in any symfony controller, use::

    $response = $this->get("ci")->getResponse($request);

This method will redirect request handling to CodeIgniter and it's routing.

The need might arise just to use some legacy CodeIgniter code to get libraries, modules or even helpers.
In that case, you can get CI instance::

    $CI = & $this->get("ci")->getInstance();
    
In case "getResponse" was called before, it will return the controller used.
Otherwise, a fake controller instance will be created. Therefore "getResponse" can not
be used if "getInstance" was called.

Symfony2 container is available to CodeIgniter like a library. For example, to get a doctrine service in 
CI controller, call::

    $this->symfony->get('doctrine');

To use Symfony2 database configuration in CodeIgniter, do this in database.php config file ::

    $connection = $GLOBALS['CI_symfony']->get('doctrine')->getConnection(); // :)
    
    $db['default']['hostname'] = $connection->getHost();
    $db['default']['username'] = $connection->getUsername();
    $db['default']['password'] = $connection->getPassword();
    $db['default']['database'] = $connection->getDatabase();

That's it for now.

Contributions are welcome.