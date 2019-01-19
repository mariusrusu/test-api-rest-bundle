# Usages

## Reuse response content

Another thing patterns allow you is to catch values from expected response to reuse them in ulterior tests. Hence , it is a feature reserved to scenario testing.

To catch a value, you need to alter the expected value of an attribute in a response with the following structure :
```json
"#caughtVariableName={{@pattern@}}"
```

* `#` is used to announce a value is been caught
* `caughtVariableName` is the name of the variable containing the caught value
* `@pattern@` is for asserting the value

For example, say that you're using an uuid in your application. You can't predict its value but you need it for a scenario test. To catch it and reuse it, modify the expected response of the post first :

```json
{
  "id":"@integer@",
  "uuid": "#uuid={{@uuid@}}",
  "name":"@name@",
  "value":418,
  "date_of_creation":"@string@.isDateTime()",
  "active":true
}
```

See that `"#uuid={{@uuid@}}"` pattern ? Not only it asserts the value is an uuid, but also saves it in an `uuid` variable. 


Now, to delete that entry using the caught variable, write your next test like that :

```yaml
    - { action: "DELETE", url: "/pattern/#uuid#", status: 204 }
```

You can reuse value either in the yaml file or in json payload file : 

```json
{
  "id":"@integer@",
  "uuid": "#uuid={{@uuid@}}",
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

Here, the `name` variable caught in the response with `#name` is reused in the next test's payload with `#name#`.

You can catch as many value you want, but keep in mind that if you use the same variable name twice, the initial value will be over-written.