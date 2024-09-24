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
        "short_url": "http://localhost/KwTgYKUh",
        "original_url": "https://euvsdisinfo.eu/ua/learn-ua/",
        "key_url": "KwTgYKUh",
        "author_name": "mykola",
        "counter_creators": 1,
        "created_at": "2024-09-21 14:36:29",
        "exists": true,
        "copy_creators": [
            {
                "author_name": "roma",
                "created_at": "2024-09-24 18:11:35"
            }
        ]
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
