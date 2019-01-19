# Usages

* [Test dynamic responses](#test-dynamic-responses)
* [What are patterns ?](#what-are-patterns)
* [Writing expected responses with patterns](#writing-expected-responses-with-patterns)
    
## Test dynamic responses

Sometimes when you test your API, you can't be assured of the exact data you get in responses. Either it is too random (as with a uuid or a password hash), either it is too precise (as with a datetime). Or sometime you even don't care of certain values in your responses, like an id. But you still need to assert that value, for constraint and security reasons. With TestApiRestBundle, it's handled with patterns thanks to the [Coduo\PHP-Matcher library](https://github.com/coduo/php-matcher). 

### What are patterns ?

Patterns are indications for TestApiRestBundle to assert to a certain scope specific values. For example, say that you want to test that a uuid value. You can't be sure of that value (that's the purpose of the uuid) and it doesn't really matter if it varies for you application. So you don't need to test precisely the value. But you can still assert:
* it's a string
* it contains a certain number of character
* it respects a certain structure, or RegExp, that you configured

Other example : the datetime. It's too precise, with those seconds, to be tests precisely. But there are patterns you can check like:
* it IS indeed a datetime
* it is set according to a period

With patterns, you can check all those conditions and more.

To be able to do it, TestApiRestBundle implements the Coduo\PHP-Matcher library. You can check [there](https://github.com/coduo/php-matcher) all the available patterns.

### Writing expected responses with patterns

To show you how to test with patterns, we made another sample project with the simple one and some modifications. You can check all of them [here](../../Tests/sampleProject/src/PatternBundle/).

So now, we have an uuid that we can't test precisely because it is random, a name and a value determined by the user and not impacting the system's reliability, a datetime too precised to be tested accurately and an active property set to true by default. With the philosophy of patterns, your expected response json file should look like :
         
```json
[
  {
    "id": 1,
    "uuid": "@uuid@",
    "name" : "@string@",
    "value": "@integer@",
    "date_of_creation": "@string@.isDateTime().before('tomorrow').after('yesterday')",
    "active": true
  },
  {
    "id": 2,
    "uuid": "@uuid@",
    "name" : "@string@",
    "value": "@integer@",
    "date_of_creation": "@string@.isDateTime().before('tomorrow').after('yesterday')",
    "active": true
  }
]
```

To know more about all the possibilities of patterns, you can refer to the PHP-matcher [documentation](https://github.com/coduo/php-matcher).