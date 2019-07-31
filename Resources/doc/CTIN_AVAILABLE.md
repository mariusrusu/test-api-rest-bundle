#CT_IN available

| mimetype         | file extension |
|------------------|----------------|
| application/json | json           |
| application/pdf  | pdf            |
| image/png        | png            |
| image/x-png      | png            |
| image/jpg        | jpg            |
| image/jpeg       | jpg            |
| image/pjpeg      | jpg            |
| others...        | bin            |

By default, if the `ct_in` is undefined, its value is `application/json`. 

In the case you want to test a `multipart/data` request, the value of the `ct_in` key must be :
```
multipart/form-data; boundary=the-value-of-the-boundary
```