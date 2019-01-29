به نام خدا

# سرویس ای‌پی‌آی برد‌امتیازات
v1.1.0



# نصب و راه اندازی سرویس
## موارد مورد نیاز
- php >= 7.0
- mysql
- Apache or Nginx 

## نحوه نصب
پس از بارگزاری فایل ها در مسیر ریشه سایت اگر از وب سرویس apache استفاده می کنید مطمئن شوید که از پرونده `.htaccess` پشتیبانی می شود.

با ویرایش فایل `index.php` و تعویض مشخصات بخش دیتابیس و قرار دادن مشخصات دیتابیس mysql خود به این شکل :

```
define('DB_SERVER_NAME', 'insert here');
define('DB_USER_NAME', 'insert here');
define('DB_PASSWORD', 'insert here');
define('DB_NAME', 'insert here');

```

با اجرای   `domain.com/install`   سرویس آماده استفاده است. ( به پیامی که هنگام اجرای این صفحه نمایش داده می شود توجه کنید )


# ارسال اطلاعات به وب‌سرویس
درخواست های ثبت امتیاز به صورت POST به این ادرس باید ارسال شود : `domain.com/set`

## شکل کلی در خواست ارسالی
این در خواست حاوی کلید های زیر است :

- token
- name
- score
- unix_time
- hash

ورودی `token`  را در فایل `index.php` به این شکل تغییر دهید :

```
define('DEFAULT_TOKEN', 'fd40c20e30d7c258f6bacfe892a5c48a3f7b954d');

```


ورودی های `name` و `score` نام بازیکن و امتیاز آن است که می خواهید ذخیره کنید.


ورودی `unix_time` [ساعت یونیکس](https://fa.wikipedia.org/wiki/%D8%B3%D8%A7%D8%B9%D8%AA_%DB%8C%D9%88%D9%86%DB%8C%DA%A9%D8%B3)
	* برای دریافت زمان امتیاز ثبت شده ما به شما به شکل ساعت یونیکس باز نخواهیم گرداند و به صورت تاریخ قابل خواندن شمسی و میلادی دریافت خواهید کرد.

## هش کردن دیتا و ارسال این هش

یکی از ورودی هایی که برای ثبت اطلاعات باید بفرستید hash می باشد این مقدار داده رمزنگاری شده از مقادیر دیگر ورودی می باشد تا کاربران به راحتی نتوانند در رتبه و امتیاز خود اختلالی به وجود بیاورند.

داده ای که برای رمزنگاری استفاده می شود باید به صورت یک ارایه از جنس json باشد ارایه ای به شکل زیر : 

```
 {"token":"TOKEN","name":" NAME","score":54232487,"unix_time":"1544521744"}

 
```

این شیء جیسان به وسیله ابزار openssl_encrypt و با کلید و  iv موجود در فایل `index.php` رمزنگاری و ارسال می شود

```
define('HASH_METHOD', 'AES-128-CBC');
// generate by : http://www.miraclesalad.com/webtools/md5.php
define('HASH_KEY', '2fa4231a009e148288114ea5dafc149f');
// has different size in every method
define('HASH_IV',  'a874a935c9680esd');

```

* توجه این متد, کلید , و iv باید هم در کلاینت و هم در سرور به صورت یکسان موجود باشد.
* این مقادیر را تغییر دهید.. لیست hash_method ها را از این ادرس می توانید بدست بیاورید : [php.net](http://php.net/manual/en/function.openssl-get-cipher-methods.php)
* هم چنین برای رمزنگاری این داده ها از این توابع در php استفاده شده است معادل انرا در زبان برنامه نویسی خود یافته و از ان استفاده کنید. [php.net](http://php.net/manual/en/function.openssl-encrypt.php)
* سایت php.net بدون پراکسی باز نمی شود.


## پیام خروجی :
در صورت صحیح بودن مقادیر و اضافه شدن امتیاز پیغام زیر به صورت یه آرایه json نمایش داده خواهد شد :
```

{"status":"ok","data":"successfuly add data"}

```
در غیر این صورت آرایه json شامل status : false و یک پیغام نمایش داده خواهد شد.


## مثال برای ارسال اطلاعات با php 
```
<?php

define('HASH_METHOD', 'AES-128-CBC');
// generate by : http://www.miraclesalad.com/webtools/md5.php
define('HASH_KEY', '2fa4231a009e148288114ea5dafc149f');
define('HASH_IV',  'a874a935c9680esd');
define('TOKEN', 'fd40c20e30d7c258f6bacfe892a5c48a3f7b954d');
define('URL', 'http://leaderb.javad/');


/**
 * SET
 */

function set_score($_array)
{
	if (!is_array($_array)) 
	{
		return false;
	}
	$hash_json	= json_encode($_array);
	$hash_data 	= openssl_encrypt($hash_json, HASH_METHOD, HASH_KEY, $options=0, HASH_IV);

	$post_data ='';
	foreach($_array as $k => $v) 
	{ 
	  $post_data .= $k . "=".$v."&"; 
	}
	$post_data .= "hash=".$hash_data."&";
	$post_data = rtrim($post_data, '&');
 	
	
	$request_headers[] = 'charset=utf-8';

	$send_curl = curl_init();
	curl_setopt($send_curl,CURLOPT_URL,URL."set/");
    curl_setopt($send_curl,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($send_curl,CURLOPT_HEADER, false); 
    curl_setopt($send_curl, CURLOPT_POST, 7);
    curl_setopt($send_curl, CURLOPT_POSTFIELDS, $post_data);    
 
	$output_curl = curl_exec($send_curl);
 
    curl_close($send_curl);

    $given_array = json_decode($output_curl,true);
    if ($given_array["status"] == "ok") 
    {
    	return true;
    }
    else
    {
    	return false;
    }

}

$my_arr = [
	'token' 	=> TOKEN,
	'name' 		=> 'javaad_gh',
	'score' 	=> 54232487,
	'unix_time' => 1544545559,
];

var_dump(set_score($my_arr));

```



# دریافت اطلاعات از وب سرویس

برای دریافت اطلاعات از سرور نیاز به رمزنگاری نیست و این کار به راحتی با ارسال درخواست GET ( حتی به شکل یک ادرس URL ) امکان پذیر است.


کلید‌های موردنیاز شامل این موارد هستند :

- token
- interval
- order
- name

خب `token` که همون توکن‌ای هست که دارید و توی set ازش استفاده کردید.


در مورد `interval` به ۲ شکل می تونید ازش استفاده کنید 
- اول`interval=all` تمام فیلد هایی که اضافه کردید رو بهتون بر می گردونه. البته می تونید بر اساس `score` (کم به زیاد و برعکس) هم دریافت کنید که با اضافه کردن `&order=DESC` یا `order=ASC` امکان پذیر هست. همچنین می تونید دیتا ها رو بر اساس یک نام کاربر خاص هم دریافت کنید `name=javad` می تونید از دو این کلید ها در کنار `interval=all` استفاده کنید که در این صورت هم براساس `score` مرتب خواهند شد هم براساس `name` نمایش داده خواهند شد. اگر تمایل دارید از طریق url فراخانی کنید به شکل زیر امکان پذیر است :
	- `http://example.com/get?token=ANY_TOKEN&interval=all&name=javad&order=DESC`
- نوع دوم `interval=custom` است که می توانید در یک بازه زمانی اطلاعات را دریافت کنید این بازه زمانی می تواند به صورت unix | shamsy | milady باشد. همچنین از `order` و `name` هم در این بخش در کنار بازه زمانی می توانید استفاده کنید. در زیر به چند مثال دقت کنید
	- `http://example.com/get?token=ANY_TOKEN&interval=custom&date_type=milady&date=2000-00-00-00:00|2001-08-18-23:59` زمان باید به صورت : `(Y-m-d-H:i)` باشد
	- `http://example.com/get?token=ANY_TOKEN&interval=custom&date_type=milady&date=2000-00-00-00:00|2001-08-18-23:59&name=javad_gh&order=ASC`
	- `http://example.com/get?token=fd40c20e30d7c258f6bacfe892a5c48a3f7b954d&interval=custom&date_type=unix&date=1544545359|1544545759&name=javaad_gh`

## خروجی دریافتی 
اگر کوئری درست و خطای mysql و.. نداشته باشید باید خروجی ای شبیه به این ( بستگی به نوع کوئری که وارد کردید دارد ) داشته باشید :
```
{
"status":"ok",
"data":[
		{
			"id":"1",
			"name":"javaad_gh",
			"date_shamsy":"944-3-28-00:00",
			"date_milady":"1565-06-17 00:00",
			"date_unix":"1565168",
			"score":"54232487",
			"timestamp":"2018-12-11 19:51:26"
		}
	]
}

```

در غیر این صورت 

```
{
	"status":"false",
	"message": "and message about problem"
}

```


#تغییرات v1.1.0
با اضافه کردن repeat=off در کوئری GET از هر کاربر یک امتیاز به نمایش در می آید. ( دقت کنید که امتیاز مقدار score به نمایش درآمده بر حسب اولین مقداریست که با کوئری بدست می آورید)


برای آزمایش هر کدام از کوئری های بالا را با `repeat=off` و بدون این اپشن امتحان کنید.


# پشتیبانی

هر کاری داشتید تماس بگیرید :)

- TELEGRAM 	: `@geeksesi_xyz`
- Email 	: `geeksesi[@]gmail.com`


# کپی رایت 
این لیدر برد به سفارش تیم بالهای سبز و برای استفاده گروه بازی سازان قم تهیه شده است .


*پکیج بخش یونیتی هنوز تهیه نشده است*


لطفا نظرات و انتقادات خود را برای تکمیل و بهبود با ما در میان بگذارید 


با تشکر تیم بالهای سبز


