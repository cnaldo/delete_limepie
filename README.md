limepie
=======

소개
--------
웹 응용 프로그램을 좀더 쉽고 안전하며 효율적으로 개발하기 위해 만들어졌습니다. 

LGPL 라이센스를 채택한 오픈 소스로 무료 제공되며, 빠르고 최소한의 자원을 사용하는 초경량 프레임워크입니다.

일반적으로 웹 응용 프로그램 제작에 요구되는 라이브러리와 안전한 구조를 제공하여, 개발 속도와 품질등 생산성을 향상시키므로 
코드의 양을 최소화, 보다 창조적인 작업에 집중할수 있게 합니다.

사용자가 접근한 URI를 '/'로 구분해 의미를 부여하고, 연관 프로그램을 호출하는 방식으로 동작 합니다.

MVC (Model-View-Controller) 디자인 패턴을 채택해 클라이언트 프로그램에서 보여지는 부분과 작동되는 기능들을 분리할수 있습니다.


ROUTE
--------

### Case #1
application 폴더 안에 클래스 파일 단위로 기능을 구현하여 사용할수 있는 구조로 URL은 아래와 같이 /controller/action에 매핑됩니다.
```php
$router = new router(array(
  '(.*)' => array(
    ':controller/:action',
    array('module' => 'application') // 기본 지정
  )
)); 
```

`GET http://example/`
  - 파일위치  :  /example/html/application/index.php  
  - 클래스명  :  application_index  
  - 매소드명  :  index or get_index  

`GET http://example/news`   
  - 파일위치  :  /example/html/application/news.php    
  - 클래스명  :  application_news    
  - 매소드명  :  index or get_index    

`GET http://example/blog/list`  
  - 파일위치  :  /example/html/application/blog.php  
  - 클래스명  :  application_blog  
  - 매소드명  :  list or get_list  

`POST http://example/blog/list`
  - 파일위치  :  /example/html/application/blog.php  
  - 클래스명  :  application_blog  
  - 매소드명  :  list or post_list  

`GET http://example/blog/list/field/date/sort/desc`
  - 파일위치  :  /example/html/application/blog.php  
  - 클래스명  :  application_blog  
  - 매소드명  :  list or get_list  
  - 매개변수  :   

  ```php
$field = $this->getQeury("field"); // date
$sort  = $this->getQeury("sort");    // desc 
$param0 = $this->getSegment(0); // blog 
$param1 = $this->getSegment(1); // list 
$param1 = $this->getSegment(2); // field 
$param2 = $this->getSegment(3); // date 
$param3 = $this->getSegment(4); // sort 
$param4 = $this->getSegment(5); // desc 
  ``` 


### Case #2
모듈 폴더안 클래스 파일의 index메소드를 기본으로 실행하는 구조로 URL은 아래와 같이 /module/controller에 매핑됩니다. 

basedir을 application로 설정하면 Case #1과는 달리 application가 모듈네임이 아니라 폴더명이 되었으므로 클래스명에서도 "application_"는 필요없습니다. 

각각의 컨트롤러 클래스의 index 메소드(action 기본 지정)를 자동 실행하므로 클래스내에 반드시 존재해야합니다. 
```php
$router = new router(array(
    '(.*)' => array(
        ':module/:controller/',
        array('basedir' => 'application', 'action' => 'index') // 기본 지정
   )
)); 
```

`GET http://example/`
  - 파일위치  :  /example/html/application/index/index.php  
  - 클래스명  :  index_index  
  - 매소드명  :  index or get_index  

`GET http://example/news` 
  - 파일위치  :  /example/html/application/news.php  
  - 클래스명  :  news_index  
  - 매소드명  :  index or get_index  

`GET http://example/blog/list` 
  - 파일위치  :  /example/html/application/blog/list.php  
  - 클래스명  :  blog_list  
  - 매소드명  :  index or get_index  

`POST http://example/blog/list` 
  - 파일위치  :  /example/html/application/blog/list.php  
  - 클래스명  :  blog_list  
  - 매소드명  :  index or post_index  

`GET http://example/blog/list/field/date/sort/desc` 
  - 파일위치  :  /example/html/application/blog/list.php  
  - 클래스명  :  blog_list  
  - 매소드명  :  index or get_index  
  - 매개변수  :  

  ```php
$field = $this->getQeury("field"); // date 
$sort  = $this->getQeury("sort");    // desc 
$param0 = $this->getSegment(0); // blog 
$param1 = $this->getSegment(1); // list 
$param1 = $this->getSegment(2); // field 
$param2 = $this->getSegment(3); // date 
$param3 = $this->getSegment(4); // sort 
$param4 = $this->getSegment(5); // desc 
  ``` 



### Case #3

좀더 복잡한 형태의 라우터 규칙을 만들어 보겠습니다. URL분리는 정규식을 이용하므로 정교한 규칙 설정이 가능합니다. 

아래는 http://example.com/param1/param2, http://example.com/param1/param2/param3/param4 등 3개의 parameter를 가변적으로 인식할수 있는 규칙입니다. ("/"와 "/"사이의 문자열을 매칭시키고 "/"를 제외한 문자열만 추출합니다. 각각은 필수가 아닙니다.) 

```php
$router = new router(array(
     '(?:([^/]+)/?)?(?:([^/]+)/?)?(?:([^/]+)/?)?(.*)' => array(
         ':module/:controller/:action/',
         '$1/$2/$3/$4/',
     )
)); 
```


### Case #4

아래의 예제는 blog 모듈과 board 모듈에 대해서 http://example.com/blog/321 등 두번째 parameter가 숫자일경우 read로 간주하게 합니다. http://example.com/blog/list/47 와 같이 두번째 parameter가 list이고 세번째 parameter가 숫자일 경우 페이지 번호로 인식하게 합니다. (순차적으로 검사를 하므로 너무 많은 규칙을 넣는것은 좋지 않습니다.) 

```php
$router = new router(array(
     '(blog|board)/(\d+)' => array( // read
         ':module/:sequence/',
         '$1/$2',
         array(':controller' => 'read')
     ),
     '(blog|board)/(list)?(:?/([\d]+))' => array( // list or list paging
         ':module/:controller/:pagenum',
         '$1/$2/$3',
     )
)); 
```

`GET http://example.com/blog/321` 
  - 파일위치  :  /example/html/application/blog/read.php  
  - 클래스명  :  blog_read  
  - 매소드명  :  index or get_index  
  - 매개변수  :  
  
  ```php
$sequence = $this->getQeury("sequence"); // 321 
$param1 = $this->getSegment(1); // 321 
  ``` 

`GET http://example.com/blog/list/47` 
  - 파일위치  :  /example/html/application/blog/list.php  
  - 클래스명  :  blog_list  
  - 매소드명  :  index or get_index  
  - 매개변수  :  

  ```php
$pagenum = $this->getQeury("pagenum"); // 47 
$param1 = $this->getSegment(2); // 47 
  ``` 


### Case #5

httpd://example.com/blog/339/field/date/sort/desc 는 아래의 라우터에 매칭됩니다.
규칙의 마지막에 "(.*)"를 넣어야 "field/date/sort/desc"를 재처리할 대상으로 판단하여 
매개변수 $field = "date"; $sort = "desc";를 얻을수 있습니다. 

```php
$router = new router(array(
     '(blog|board)/(\d+)?(.*)' => array( // read
         ':module/:controller/',
         '$1/$2/$3',
     ),
)); 
```

`GET http://example/blog/list/field/date/sort/desc` 
  - 파일위치  :  /example/html/application/blog.php  
  - 클래스명  :  application_blog  
  - 매소드명  :  list or get_list  
  - 매개변수  :  
  
  ```php
$field = $this->getQeury("field"); // date 
$sort  = $this->getQeury("sort");    // desc 
$param0 = $this->getSegment(0); // blog 
$param1 = $this->getSegment(1); // list 
$param1 = $this->getSegment(2); // field 
$param2 = $this->getSegment(3); // date 
$param3 = $this->getSegment(4); // sort 
$param4 = $this->getSegment(5); // desc 
  ``` 



아래의 예에서처럼 '(.*)'가 규칙의 마지막에 없을경우 :module 가 $1, :controller 가 $2에 대입되고 'field/date/sort/desc'등 나머지를 처리할 룰이 없으므로 매개변수 seg의 값이 null이 됩니다. 

```php
$router = new router(array(
     '(blog|board)/(\d+)' => array( // read
         ':module/:controller/',
         '$1/$2',
     ),
)); 
```

`GET http://example/blog/list/field/date/sort/desc` 
  - 파일위치  :  /example/html/application/blog.php  
  - 클래스명  :  application_blog  
  - 매소드명  :  list or get_list  
  - 매개변수  :  

  ```php
$field = $this->getQeury("field"); // null 
$sort  = $this->getQeury("sort");    // null 
$param0 = $this->getSegment(0); // blog 
$param1 = $this->getSegment(1); // list 
$param1 = $this->getSegment(2); // field 
$param2 = $this->getSegment(3); // date 
$param3 = $this->getSegment(4); // sort 
$param4 = $this->getSegment(5); // desc 
  ``` 
