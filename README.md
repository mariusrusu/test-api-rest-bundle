TestApiRestBundle
=================

[![Build Status](https://travis-ci.com/everycheck/test-api-rest-bundle.svg?branch=master)](https://travis-ci.com/everycheck/test-api-rest-bundle)

## About


TestApiRestBundle allows you to test your Symfony REST API deeply. It checks the validity of your application and ensures it stays robust throughout time using PHPUnit and via unit, scenario and database testing.

# Table of contents

* [About](#about)
* [Installation](#installation)
* [Configuration](#configuration)
* [Usages](#usages)
* [Tests folder structure](#tests-folder-structure)
* [Launching test](#launching-test)


## Installation


You can install using composer, assuming it's already installed globally : 

```
composer require --dev everycheck\test-api-rest-bundle
```

## Configuration

Configure the relative paths of the directories containing the requests payloads that you would send in your tests and their expected responses. The paths are taken from `AcmeBundle\tests\AcmeBundle`.

```yaml
test_api_rest:
    directory:
        payloads: path/to/payloads
        responses: path/to/responses
``` 

So, here, the real path of the directories  are : 

`
AcmeBundle\tests\AcmeBundle\path\to\payloads
AcmeBundle\tests\AcmeBundle\path\to\responses
`

## Usages

* [Basic usages](Resources/doc/SIMPLE_USAGE.md)

### Advanced usages

* [Test dynamic responses](Resources/doc/PATTERN_USAGE.md)
* [Reuse response content](Resources/doc/REUSE_USAGE.md)
* [Test email sending and their content](Resources/doc/EMAIL_USAGE.md)


## Tests folder structure

To test one of your bundle, you just have to replicate the structure of that one and add some directories by the following structure

    .
    ├── tests                                       #   The basic Symfony test directory
    │   ├── AcmeFooBundle                           #   Name of your bundle
    │   │   ├── Controller                          
    │   │   │   ├── FooControllerTest.php           #   Controller your want to test
    │   │   │   └── ...                                    
    │   │   │
    │   │   ├── DataFixtures                        
    │   │   │   └── ORM                             
    │   │   │       ├─ LoadAcmeFooFixtures.php     #   Fixtures dedicated to one controller   
    |   |   |       └── ...
    │   │   │                                       
    │   │   ├── Payloads                            
    │   │   │   └── ...                             #   File posted to your API
    │   │   │                                       
    │   │   ├── Resources                           
    │   │   │   └── config                          
    │   │   │       ├── foo.yaml                    #   All the tests for a specific controller, set as a YAML file
    │   │   │       └── ...                    
    │   │   │   
    │   │   └── Responses                           #   All the expected responses coming from the endpoints of your API when testing it
    │   │       └── Expected
    │   │           └── ...
    │   └──
    └──


## Launching test 

To test the bundle type : 

```
composer install
./vendor/bin/phpunit
```
