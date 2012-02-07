What it is
==========

Integrates CodeIgniter as a bundle in Symfony 2.

Usage
=====

Install bundle over composer, and load it in AppKernel::

    $bundles = array(
        ...
        new Nercury\CodeIgniterBundle\NercuryCodeIgniterBundle(),
        ...
    );

In config.yml, specify paths to CodeIgniter directories::

    nercury_code_igniter:
        application_path: %kernel.root_dir%/../CodeIgniter_210/application
        system_path: %kernel.root_dir%/../CodeIgniter_210/system

Bundle uses a bit of code from CodeIgniter 2.1.0, so it should work the best with 2.1.0 version.
In case it fails, relevant code is in CiHelperService::getResponse and ci_bootstrap.php.
To get CI response in symfony controller, use::

    $response = $this->get("nercury_code_igniter.helper")->getResponse($request);

Thats it for now. Patches are welcome.