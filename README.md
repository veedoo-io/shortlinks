## About Short Link

#### Api create short link
> `FAKE_URL_LENGTH`=`8` - length `key_url`
> `REDIRECT_URL`=`https://euvsdisinfo.eu` - redirect start page

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
        "short_url": "http://localhost/api/lgvXYK30",
        "original_url": "https://www.google.com",
        "key_url": "lgvXYK30",
        "author_name": null,
        "counter_creators": 0,
        "created_at": "2024-09-10 07:38:07"
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
