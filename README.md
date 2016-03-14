# YouTube

Youtube video download class for Laravel 5.1+. Made available by The Coding Company

https://thecodingcompany.se

Build by:  Victor Angelier <vangelier \u0040 hotmail.com>

#Install/Composer

Easy:  composer require thecodingcompany/youtube

#Example
```
chmod 0777 public/video

$file = YouTube::downloadYTVideo("mHeK0Cwr9sg");
echo $file; <-- Will give you the full public path.

```
