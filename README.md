## About Short Link

#### Api create short link
> `FAKE_URL_LENGTH`=`8` - length `key_url`

> `POST` http://localhost/api/url
###### `parameters`

 ```
 url: required,string - https://www.google.com
 author_name: nullable,string - laravel 
 ```

###### `answer api create`
```
{
    "success": true,
    "data": {
        "short_url": "http://localhost/api/eSppNrIw",
        "original_url": "https://www.google.com",
        "key_url": "eSppNrIw",
        "author_name": "laravel"
    },
    "message": "ok"
}
```

#### Api redirect link by key url

> `GET` http://localhost/api/{key_url}

###### `parameters`

 ```
 key_url: eSppNrIw
 ```
> redirect 301 by `original_url` to page https://www.google.com
