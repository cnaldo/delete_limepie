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
<?php

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
  <?php

  $field  = $this->getParam("field"); // date
  $sort   = $this->getParam("sort");    // desc 
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
<?php

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
  <?php

  $field  = $this->getParam("field"); // date 
  $sort   = $this->getParam("sort");    // desc 
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
<?php

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
<?php

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
  <?php

  $sequence = $this->getParam("sequence"); // 321 
  $param1   = $this->getSegment(1); // 321 
  ``` 

`GET http://example.com/blog/list/47` 
  - 파일위치  :  /example/html/application/blog/list.php  
  - 클래스명  :  blog_list  
  - 매소드명  :  index or get_index  
  - 매개변수  :  

  ```php
  <?php

  $pagenum = $this->getParam("pagenum"); // 47 
  $param1  = $this->getSegment(2); // 47 
  ``` 


### Case #5

httpd://example.com/blog/339/field/date/sort/desc 는 아래의 라우터에 매칭됩니다.
규칙의 마지막에 "(.*)"를 넣어야 "field/date/sort/desc"를 재처리할 대상으로 판단하여 
매개변수 $field = "date"; $sort = "desc";를 얻을수 있습니다. 

```php
<?php

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
  <?php

  $field  = $this->getParam("field"); // date 
  $sort   = $this->getParam("sort");    // desc 
  $param0 = $this->getSegment(0); // blog 
  $param1 = $this->getSegment(1); // list 
  $param1 = $this->getSegment(2); // field 
  $param2 = $this->getSegment(3); // date 
  $param3 = $this->getSegment(4); // sort 
  $param4 = $this->getSegment(5); // desc 
  ``` 



아래의 예에서처럼 '(.*)'가 규칙의 마지막에 없을경우 :module 가 $1, :controller 가 $2에 대입되고 'field/date/sort/desc'등 나머지를 처리할 룰이 없으므로 매개변수 seg의 값이 null이 됩니다. 

```php
<?php

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
  <?php

  $field  = $this->getParam("field"); // null 
  $sort   = $this->getParam("sort");    // null 
  $param0 = $this->getSegment(0); // blog 
  $param1 = $this->getSegment(1); // list 
  $param1 = $this->getSegment(2); // field 
  $param2 = $this->getSegment(3); // date 
  $param3 = $this->getSegment(4); // sort 
  $param4 = $this->getSegment(5); // desc 
  ``` 





CONTROLLER
----------

리퀘스트 프로세싱 로직으로 비지니스 로직(모델)과 프리젠테이션 로직(뷰)을 연결해 줍니다. 

URI는 ROUTE를 거쳐 사용자 컨트롤러 클레스의 액션 메소드를 동작시킵니다. 사용자 컨트롤러 클레스는 반드시 부모 컨트롤러 클레스로 부터 상속(extends) 받아야 하며 그렇지 않을 경우 컨트롤러의 기능을 사용할 수 없습니다. 

```php
<?php
// application_blog.php

class application_blog extends controller {
     function get_list() {
         echo "Hello World!";
     }
} 
```


컨트롤러 클래스는 서브 클래스를 만들어 컨트롤러 클래스의 기반이되는 인터페이스와 기능을 새롭게 정의 수 있습니다. 아래는 모든 페이지에서 접속자의 회원정보를 검사하기 위해 컨트롤러 클래스를 확장한 예제입니다. 

```php
<?php
// my_controller.php

class my_controller extends controller {
     public $user = array();    // 접속자의 회원정보

    function __construct() {   // 생성자를 사용한다면
        parent::__construct(); // 반듯이 부모 컨트롤러 클래스의 생성자를 호출해야함 

        $user_id = cookie::get("user_id");
         $this->user = $this->getUserInfo($user_id); // 접속자의 회원정보
    }

     function getUserInfo($id) {
         return array("......");
     }
} 
```

```php
<?php
// application_blog.php

class application_blog extends my_controller {
     function get_list() {
         echo $this->user." Hello World!";
     }
} 
```



### 매개변수

URI로 매개변수를 얻는 방법은 3가지가 있습니다. 


####※ segment

segment 는 URI에서 0부터 1씩증가하는 형태로 순서대로 접근하여 매개변수를 얻습니다. 

`GET http://example/blog/list/date/desc` 

```php
<?php

class application_blog extends controller {
    function get_list() {
        echo $this->getSegment(0); // blog
        echo $this->getSegment(1); // list
        echo $this->getSegment(2); // date
        echo $this->getSegment(3); // desc
    }
} 
```

####※ parameter

parameter 는 ROUTE에서 module, controller, action등에 매칭된 나머지로 짝을 맺어 매개변수를 얻습니다. 

`GET http://example/blog/list/field/date/sort/desc` 

```php
<?php

class application_blog extends controller {
    function get_list() {
        // blog는 controller
        // list는 action
        echo $this->getParam("field"); // date
        echo $this->getParam("sort");  // desc
    }
}
```


####※ argument

메소드의 argument 로 부터 매개변수를 얻습니다. 

`GET http://example/blog/list/date/desc` 

```php
<?php

class application_blog extends controller {
    function get_list($controller, $action, $field, $sort) {
        echo $controller; // blog
        echo $action;     // list
        echo $field;      // date
        echo $sort;       // desc
    }
}
```


### 에러 처리 컨트롤러

프레임웍은 서버상에 실제 존재하는 파일을 실행하는 것이 아니라 URI 요청을 ROUTE의 분석에 의해 사용자 컨트롤러 클레스의 액션 메소드를 실행하여 동작시키므로 웹서버가 자체적으로 보여주는 에러페이지들을 사용할수 없고, 제공되는 에러 처리 컨트롤러를 사용하거나 확장하여 에러페이지를 작성하여야 합니다.

