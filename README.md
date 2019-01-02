TestApiRestBundle
=================

##About


TestApiRestBundle allows you to test your Symfony REST API deeply. It checks the validity of your application and ensures it stays robust throughout time via unit, scenario, file and database testing.

##Installation


You can install using composer, assuming it's already installed globally : 

```
composer require --dev everycheck\test-api-rest-bundle
```

##Usages

###Create a simple demo app
To demonstrate to you how to use the bundle, we have created a sample project. You can find it in the ```/Doc/Example/sampleProject``` and reproduce it to try our bundle.

Lets look to the Entity first. To stay basic, it's only defined by 2 properties, a name and a value : 

```php
<?php

/**
 * Demo
 *
 * @ORM\Table(name="demo")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DemoRepository")
 */
class Demo
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="value", type="integer")
     */
    private $value;
}

?>
```

Now that we can store data, what if we wanted to use it ? Let's create a Controller, with a GET route to display our Entity.

```php
<?php

/**
 * Demo controller.
 *
 * @Route("demo")
 */
class DemoController extends Controller
{
    /**
     * Lists all demo entities.
     *
     * @Route("", name="demo_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $demos = $em->getRepository('AppBundle:Demo')->findAll();
        $response = $serializer->serialize($demos, 'json');

        return new Response($response, 200, ['Content-Type'=>"application/json"]);
    }
}

?>
```

Its purpose is simple : retrieve all the informations of the Demo Entity and display them. As you can see, it doesn't send a twig template as a response but a serialized one in json format. It's an API REST after all. 

But it would be better if we can also post some data. That's what the POST route is made for : 

```php
<?php

    /**
     * Creates a new demo entity.
     *
     * @Route("/new", name="demo_new")
     * @Method({"POST"})
     */
    public function newAction(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $demo = new Demo();
        $form = $this->createForm(DemoType::class, $demo);
        $form->submit($data);

        if(!$form->isValid())
        {
            return $this->badRequest("Invalid form");
        }

        $em = $this->getDoctrine()->getManager();

        $em->persist($demo);
        $em->flush();

        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $response = $serializer->serialize($demo, 'json');
        return new Response($response, 201, ['Content-Type'=>"application/json"]);
    }

?>
```

And if you want to erase some old or wrong data, we included a DELETE route to do so :

```php
<?php

?>
```

So. Now we have an Entity to store data and a Controller with routes to use it, let's write some unit test to assure our application is working as it should.

###Preparation for the tests

Before we start writing tests, some preparation is necessary. Go to your ```app/config/config_test.yaml```. To make sure the tests don't take hours, we use sqlite database. So in your config file, replace the mysql driver with the sqlite one : 
```yaml
doctrine:
    dbal:
        driver:   pdo_sqlite
        url: '%database_url%'
        charset:  UTF8
        server_version: 5.6
    orm:
        auto_generate_proxy_classes: "%kernel.debug%"
        naming_strategy: doctrine.orm.naming_strategy.underscore
        auto_mapping: true
```

And in your ```app/config/parameters.yml```, make sure that you are using an sqlite url like the following:

```yaml
database_url: 'sqlite:///%kernel.root_dir%/../var/data/db_test/sampleproject.db3'
```

Once its done, you can generate the file with the classic command ```bin/console doctrine:database:create``` and add our demo entity to it with a ```bin/console doctrine:schema:update --force```. 

##Tests folder structure

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

