# Usages

* [Basic usages with a simple demo app](#basic-usages-with-a-simple-demo-app)
* [Databsae preparation for the tests](#database-preparation-for-the-tests)
* [Writing a ControllerTest](#writing-a-controllertest)
* [Writing tests](#writing-tests)
    * [Unit testing](#unit-testing)
    * [Scenario testing](#scenario-testing)
    
## Basic usages with a simple demo app
To demonstrate to you how to use the bundle, we have created a sample project. You can find it [here](../../Tests/sampleProject/src/DemoBundle) and reproduce it to try our bundle.

Lets look to the [Demo](../../Tests/sampleProject/src/DemoBundle/Entity/Demo.php) Entity first. To stay basic, it's only defined by 2 properties, a name in string and a value in int.

Now that we can store data, what if we wanted to use it ? Let's create a [DemoController](../../Tests/sampleProject/src/DemoBundle/Controller/DemoController.php). As TestApiRestBundle only handle json response, the controller won't return anything but serialized object. Here's an example with a GET route, displaying all the data stored in the Demo Entity.

```php

    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $demos = $em->getRepository('AppBundle:Demo')->findAll();
        $response = $serializer->serialize($demos, 'json');

        return new Response($response, 200, ['Content-Type'=>"application/json"]);
    }

```

Do the same for a POST and a DELETE route, and our simple app become pretty complete. But before writing our first test we still have a few preparation to do.

### Database preparation for the tests

As each tests are independant, the database must be reload at the beginning of them to an equivalent state. The classic method is to destroy the database, re-create it with its scheme and its fixtures data. Bigger the database is, longer the time the tests take to process. So, for performance and productivity reason, TestApiRestBundle acts differently : it reloads the database state at each setup using files. 

All you need to have is a `database_url` set in your doctrine configuration.

&#9888; ___Only mysql and sqlite mode are supported by TestApiRestBundle.___

Before launching the tests, you must prepare the database by using `bin/console test:database:prepare fixturesname`. For each fixtures file your application contains, it will apply the following .:

```bash
bin/console d:d:d --force
bin/console d:d:c 
bin/console d:s:c 
bin/console d:s:u --no-interaction          #If doctrine migration isn't available
bin/console d:m:m --no-interaction
bin/console d:f:l --append
```

In short, it creates a database files, in .sql and by dumping it in mysql mode or in .sqlite otherwise, per fixtures. Those files are used between each tests to reset the database state. Hence, all the tests are independent and it takes way less time to process.

&#9888; *Be aware that in mysql mode the tests are more rigorous as it takes into account the relations constraints. But they are much slower to process than in sqlite mode due to the resetting method.*

### Writing a ControllerTest

Each of your tests are located in the `tests` folder of your application as it should be. For each of your controller you want tested, create a class in `tests\Controller` with the name of it and the suffix `Test`. In our exemple, let's say `DemoControllerTest`. And make it extend [JsonApiAsArrayTestCase](../../Controller/JsonApiAsArrayTestCase.php) to use our powerful tools.

TestApiRestBundle uses the concept of dataProvider to load the tests. But for readability reason, you won't hard write the array containing all your tests in your ControllerTest. So TestApiRestBundle would rather load an organized yaml file and parse it : 

```php

const YAML_PROVIDER_FILENAME = "demo";
    
public static function ApiCallProvider()
{
    return ResourcesFileLoader::testCaseProvider(__DIR__,self::YAML_PROVIDER_FILENAME);
}
?>
```

We said earlier that TestApiRestBundle creates a database file for each fixture you have. To use it while the reset of your database, add the setUp method : 

```php

const FIXTURE_FILENAME = "LoadDemoFixtures";
    
public function setUp($fixtureFilename = null)
{
    parent::setUp(self::FIXTURE_FILENAME);
}
?>
```

Finally, to execute the tests and make assertions, you juste need to add the testAPICall function and pass it the dataProvider annotation to use ApiCallProvider.


```php

/**
 * @dataProvider ApiCallProvider
 */
public function testAPICall($data_test)
{
    $this->genericTestAPICall($data_test);
}

?>
```

And here's the completed class :

```php
<?php

namespace EveryCheck\TestApiRestBundle\Doc\Example\sampleProject\tests\Controller;

use EveryCheck\TestApiRestBundle\Controller\JsonApiAsArrayTestCase;
use EveryCheck\TestApiRestBundle\Loader\ResourcesFileLoader;

class DemoControllerTest extends JsonApiAsArrayTestCase
{
    const YAML_PROVIDER_FILENAME = "demo";
    const FIXTURE_FILENAME = "LoadDemoFixtures";

    /**
     * @dataProvider ApiCallProvider
     */
    public function testAPICall($data_test)
    {
        $this->genericTestAPICall($data_test);
    }

    public static function ApiCallProvider()
    {
        return ResourcesFileLoader::testCaseProvider(__DIR__,self::YAML_PROVIDER_FILENAME);
    }

    public function setUp($fixtureFilename = null)
    {
        parent::setUp(self::FIXTURE_FILENAME);
    }

}

```

You can use this template in your own application and adapt the constants values and class name to your files.


### Writing tests

With TestApiRestBundle, you can test with 2 different ways. You can either test an unique kind of request to an endpoint to assert the comportment of that endpoint specifically. Since it is unique, we call it unit_test and TestApiRestBundle reset the database test after each one on them. Either you can  test a bunch of request, sent in a particular order and without database resetting between them, to test globally the comportment of your system. We call that pack of test a scenario.

#### Unit testing
The tests aren't actually written in the ControllerTest class but in a yaml file, in the ```Resources\config``` folder. To write your first unit test, start with the following tag: 

```yaml
unit_tests:
```

Each time you want to write an unit test, do it under the ```unit_tests``` tag.

Unit tests are separated by route methods.

&#9888; ___For now, GET, POST, PATCH, PUT and DELETE methods are suppported for unit testing___

So, let's say you want to test a DELETE route. To do so, add a DELETE tag : 

```yaml
unit_tests:
    DELETE:
```

A unit test is then defined by an array with keys to . Here's the list of all the possible key :

| Key       | Purpose                                                     | Default value    |
|-----------|-------------------------------------------------------------|------------------|
| url       | to which endpoint the request is sent                       |                  |
| out       | the expected response                                       |                  |
| in        | the data sent with the request                              |                  |
| status    | html code of the response                                   | 200              |
| headers   | custom headers sent with the request                        |                  |
| ct_in     | specify the content-type of the request                     | application/json |
| ct_out    | specify the content-type of the response                    | application/json |
| mail      | the expected number of email sent at the end of the request |                  |
| pcre_mail | assert the presence of a value in an sent email via RegExp  |                  |

So, if you want to test that your app send a 404 html code when you try to delete a non-found resource, you'd write the following test :

```yaml
    - { url: "/demo/a"  , status: 404 }
```

Now, add a POST tag and a new test under it :

```yaml
unit_tests:
  POST:
    - { url: "/demo/new", status: 201, in: "postValidDemo" , out: "postedDemo" }
  DELETE:
    - { url: "/demo/a"  , status: 404 }

```

As you can see, the `in` and `out` keys doesn't contain actual data, but the name of a json file. That's where you write the body of a request or of a response, for readability matters. So let's create them.


The in refers to the actual body content of the request you would send to your endpoint. The out corresponds to the response you should get from that request. By defaut, those files are stored in the `tests\Payloads` and the `tests\Responses\Expected`folder. By editing your `conf.yaml` you can change that default folder. You can also modify it for a particular test : 

```yaml
unit_tests:
  POST:
    - { url: "/demo/new", status: 201, in: "../path/to/payloads/postValidDemo" , out: "../../path/to/expected/responses/postedDemo" }

```
When this test is processed, TestApiRestBundle checks if those json data match or not.

You can write as many unit tests as you want but keep in mind the structure of the file :

```yaml
unit_tests:
  GET:
    - { url: "/demo"    , status: 200, out: "listOfDemos"}

  PATCH:
    - { url: "/demo/1"  , status: 405}

  PUT:
    - { url: "/demo/1"  , status: 405}

  POST:
    - { url: "/demo/new", status: 400, in: "postInvalidDemo" }
    - { url: "/demo/new", status: 201, in: "postValidDemo" , out: "postedDemo" }

  DELETE:
    - { url: "/demo/a"  , status: 404 }
    - { url: "/demo/1"  , status: 204 }
```

#### Scenario testing

Scenario tests are supposed to ensure the global comportment of your app. They are nothing more than a pack of unit tests without database reset between them. Before writing a scenario, add the tag at the end of your unit tests :

```yaml
unit_tests:
  GET:
    - { url: "/demo"    , status: 200, out: "listOfDemos"}

  PATCH:
    - { url: "/demo/1"  , status: 405}

  PUT:
    - { url: "/demo/1"  , status: 405}

  POST:
    - { url: "/demo/new", status: 400, in: "postInvalidDemo" }
    - { url: "/demo/new", status: 201, in: "postValidDemo" , out: "postedDemo" }

  DELETE:
    - { url: "/demo/a"  , status: 404 }
    - { url: "/demo/1"  , status: 204 }

scenario:
```

A scenario is then defined by its name and the same kind of arrays that you used for unit tests :

```yaml
scenario:
  creation_then_deletion:
    - { action: "DELETE", url: "/demo/3", status: 404}
    - { action: "POST"  , url: "/demo/new"  , status: 201, in: "postValidDemo" , out: "postedDemo"}
    - { action: "DELETE", url: "/demo/3", status: 204 }
```

You can use all the keys of the unit tests, plus another one : the `action`.

| Key       | Purpose                                                     | Default value    |
|-----------|-------------------------------------------------------------|------------------|
| action    | which method must be performed for the request              |                  |
| url       | to which endpoint the request is sent                       |                  |
| out       | the expected response                                       |                  |
| in        | the data sent with the request                              |                  |
| status    | html code of the response                                   | 200              |
| headers   | custom headers sent with the request                        |                  |
| ct_in     | specify the content-type of the request                     | application/json |
| ct_out    | specify the content-type of the response                    | application/json |
| mail      | the expected number of email sent at the end of the request |                  |
| pcre_mail | assert the presence of a value in an sent email via RegExp  |                  |

Its purpose is the specify which method must be used by the request. And speaking of methods, scenario testing also support GET, POST, PATCH, PUT and DELETE and a new one called DB. This one is used to ensure that our app acts well even in the database :

```yaml
scenario:
  db_testing:
    - { action: "POST"  , url: "/demo/new"  , status: 201, in: "postValidDemo" , out: "postedDemo"}
    - { action: "DB", url: "TestApiRestBundle:Demo?id=3", out: "dbTesting" }
```

The url here is quite different. It is defined by the entity you want to check, then a `?` and the property to check. In the example above, the scenario is checking that after a POST, there is in the database a Demo with 3 as its id.
