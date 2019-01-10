#Usages

* [Advanced usages by checking responses pattern](#markdown-header-advanced-usages-by-checking-responses-pattern)
* [What are patterns ?](#markdown-header-what-are-patterns)
* [Writing expected responses with patterns](#markdown-header-writing-expected-responses-with-patterns)
* [Use value catching and reuse with patterns](#markdown-header-use-value-catching-and-reuse-with-patterns)
    
##Advanced usages by checking responses pattern

Sometimes when you test your API, you can't be assured of the exact data you get in responses. Either it is too random (as with a uuid or a password hash), either it is too precise (as with a datetime). Or sometime you even don't care of certain values in your responses, like an id. But you still need to assert that value, for constraint and security reasons. With TestApiRestBundle, it's handled with patterns

###What are patterns ?

Patterns are indications for TestApiRestBundle to assert to a certain scope specific values. For example, say that you want to test that a uuid value. You can't be sure of that value (that's the purpose of the uuid) and it doesn't really matter if it varies for you application. So you don't need to test precisely the value. But you can still assert:
* it's a string
* it contains a certain number of character
* it respects a certain structure, or RegExp, that you configured

Other example : the datetime. It's too precise, with those seconds, to be tests precisely. But there are patterns you can check like:
* it IS indeed a datetime
* it is set according to a period

With patterns, you can check all those conditions and more.

To be able to do it, TestApiRestBundle implements the Coduo\PHP-Matcher library. You can check [there](https://github.com/coduo/php-matcher) all the available patterns.

###Writing expected responses with patterns

To show you how to test with patterns, we made another sample project with the simple one and some modifications. Basically, we added a datetime and a boolean property to the entity and the serialization from [JMSSerializerBundle](https://jmsyst.com/bundles/JMSSerializerBundle). 

&#9888;	___It is indeed easier to use patterns with the more powerfull serializer from JMS than with the default Symfony one. So we recommend to use it.___


You can check all the modifications [there](../Tests/sampleProject/src/PatternBundle/).

So, now, when you persist a new entity, you persist with it its date of creation and whether it is active or not. By default, the date is set with ```new \DateTime('now')```. You can't be sure of the value inside that property, so you can't test it with :

```json
[
  {
    "id": 1,
    "name" : "something",
    "value": 800813,
    "date_of_creation": "2019-01-10T11:12:20-00:00",
    "active": true
   }
]
```

What you are sure of, it is that a datetime is a string. So you can start by testing it with a pattern :

```json
{
  "date_of_creation": "@string@"
}
```

 But a datetime is more than just a string. And you need to be sure that your system is handling a real datetime and not any kind of string. Soo add :

```json
{
  "date_of_creation": "@string@.isDateTime()"
}
``` 

Patterns are so powerful that they do not only test the variable kind, but also expand their test capacities with functions. You can add as many expanders as you want, as much as they stay coherent between them :

```json
{
  "date_of_creation": "@string@.isDateTime().before('tomorrow').after('yesterday)"
}
``` 

Here, we are testing that the datetime value is contained between yesterday and tomorrow.

So what about our entity in its whole ? We have an id that we can test precisely, a name and a value determined by the user and not impacting the system's reliability, a datetime too precised to be tested accurately and a active property set to true by default. With the philosophy of patterns, your expected response json file should look like :

```json
[
  {
    "id": 1,
    "name" : "@string@",
    "value": "@integer@",
    "date_of_creation": "@string@.isDateTime().before('tomorrow').after('- 100day')",
    "active": true
  },
  {
    "id": 2,
    "name" : "@string@",
    "value": "@integer@",
    "date_of_creation": "@string@.isDateTime().before('tomorrow').after('- 100day')",
    "active": true
  }
]
```

###Use value catching and reuse with patterns

Another thing patterns allow you is to catch values from expected response to reuse them in ulterior tests. Hence , it is a feature reserved to scenario testing. 

For example, say that you have a huge amount of entries for an entity in your database. There's so many you're not able to predict the id of the next posted. But you need it for a scenario test. To catch it and reuse it, modify the expected response of the post first :

```json
{
  "id":"#id={{@integer@}}",
  "name":"posted",
  "value":418,
  "date_of_creation":"@string@.isDateTime()",
  "active":true
}
```

See that ```"#id={{@integer@}}"``` patter ? Not only it asserts the value is an integer, but also save it in an ```id``` variable. Now, to delete that entry using the catched variable, write your next test like that :

```yaml
    - { action: "DELETE", url: "/pattern/#id#", status: 204 }
```

So a complete scenario test will look like :

```yaml
creation_then_deletion:
    - { action: "POST"  , url: "/pattern/new"  , status: 201, in: "postValidPattern" , out: "postedPattern"}
    - { action: "DELETE", url: "/pattern/#id#", status: 204 }
```

You can reuse value either in the yaml file or in json payload file : 

```yaml
  post_with_catched_value:
    - { action: "POST"  , url: "/pattern/new"  , status: 201, in: "postValidPattern" , out: "postedPattern"}
    - { action: "POST"  , url: "/pattern/new"  , status: 201, in: "postValidPatternWithCatchedValue" , out: "postedPatternWithCatchedValue"}
```

```json
{
  "id":"#id={{@integer@}}",
  "name":"#name={{posted}}",
  "value":418,
  "date_of_creation":"@string@.isDateTime()",
  "active":true
}
```

```json
{
  "name" : "#name#",
  "value": 418
}
```

You can catch as many value you want, but keep in mind that if you use the same variable name twice, the initial value will be over-written.