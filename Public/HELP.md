# KotoriFramework使用手册

------
## 序言

### 版权申明

发布本资料须遵守开放出版许可协议 1.0 或者更新版本。
未经版权所有者明确授权，禁止发行本文档及其被实质上修改的版本。 未经版权所有者事先授权，禁止将此作品及其衍生作品以标准（纸质）书籍形式发行。
本文档的版权归KotoriFramework所有，本文档及其描述的内容受有关法律的版权保护，对本文档内容的任何形式的非法复制，泄露或散布，将导致相应的法律责任。

### 其它申明

构建框架本身并不是为了所谓的重复造轮子，更多考虑自我提升和实践。很多时候只有亲身尝试做过，才会发现自己的不足，才有可能不断的进行自我完善。发布框架本身也仅供学习参考，并未涉及其他层面的考量。

框架整体实现上或多或少参考了一些个人熟悉的框架模式，但绝非照抄照搬，总体而言是在借鉴的基础上做更多的自己认可的架构调整。个人并不觉得完全的自主创新是完美的方案，否则更像是重复的造轮子工程。能够把握细节，大同小异的基础上构建自己的创新点，不仅仅可以减少熟悉和使用框架的成本，更加减少不必要的探索和挖掘道路的成本。

---

## 基础

### 简介
本框架是一个Controller-View框架，非MVC框架，部分代码来源于ThinkPHP和codeigniter。本文档使您快速熟悉本框架。
KotoriFramework无需任何安装，直接拷贝到你的电脑或者服务器的WEB运行目录下面即可。

### 环境要求

框架本身没有什么特别模块要求，具体的应用系统运行环境要求视开发所涉及的模块。KotoriFramework底层运行的内存消耗极低，而本身的文件大小也是轻量级的，因此不会出现空间和内存占用的瓶颈。

#### PHP版本要求

PHP5.4以上版本（支持PHP7）

#### 支持的服务器和数据库环境

* 支持Windows/Unix服务器环境
* 可运行于包括Apache、IIS和nginx在内的多种WEB服务器和模式
* 支持Mysql数据库和连接

### 目录结构

> htdocs  WEB部署目录（或者子目录）  
> ├─index.php         入口文件  
> ├─App                应用目录  
> └─Kotori.class.php  核心文件  

### 开发规范

* 类文件都是以.class.php为后缀（这里是指的KotoriFramework内部使用的类库文件，不代表外部加载的类库文件），使用驼峰法命名，并且首字母大写，例如 DbMysql.class.php；
* 确保文件的命名和调用大小写一致，是由于在类Unix系统上面，对大小写是敏感的；
* 类名和文件名一致（包括上面说的大小写一致），例如 UserController类的文件命名是UserController.class.php， InfoModel类的文件名是InfoModel.class.php， 并且不同的类库的类命名有一定的规范；
* 函数的命名使用小写字母和下划线的方式，例如 get_client_ip；
* 方法的命名使用驼峰法，并且首字母小写或者使用下划线“_”，例如 getUserName，_parseType，通常下划线开头的方法属于私有方法；
* 属性的命名使用驼峰法，并且首字母小写或者使用下划线“_”，例如 tableName、_instance，通常下划线开头的属性属于私有属性；
* 以双下划线“__”打头的函数或方法作为魔法方法，例如 __call 和 __autoload；
* 常量以大写字母和下划线命名，例如 HAS_ONE和 MANY_TO_MANY；
* 数据表和字段采用小写加下划线方式命名，并注意字段名不要以下划线开头，例如 test_user 表和 user_name字段是正确写法，类似 _username 这样的字段是不合规范的。

### 开发建议

* 遵循框架的命名规范和目录规范；
* 多看看日志文件，查找隐患问题；
* 养成使用input函数获取输入变量的好习惯；

### 入口文件

KotoriFramework采用单一入口模式进行项目部署和访问，无论完成什么功能，都通过入口文件进行请求。

#### 入口文件定义

入口文件主要完成：
* 定义项目路径（必须）
* 定义系统相关常量（可选）
* 载入框架核心文件（必须）

---

## 配置

### 配置加载

项目配置需要在入口文件传递给KotoriFramework，目前支持的配置如下：
（以下列出的是所有配置项，无需全部填写，部分配置项带有默认值，如果您的要求不高，只需修改clone下来的index.php中已有的配置即可。）

```php
require './Kotori.class.php';
Kotori::run(array(
    'APP_DEBUG' => false,//开发模式
    'APP_PATH' => './App/', //项目目录
    'DB_TYPE' => 'mysql',//数据库类型 必须指定，没有的话默认不启用数据库
    'DB_HOST' => '127.0.0.1',//数据库主机
    'DB_USER' => 'root',//数据库用户名
    'DB_PWD' => 'root',//数据库密码
    'DB_NAME' => 'typecho',//数据库名
    'DB_PORT' => 3306,//数据库端口
    'DB_CHARSET' => 'utf8',//数据库收集类型
    'USE_SESSION' => true,//全局SESSSION配置
    'URL_MODE' => 'QUERY_STRING',//默认URL模式为QUERY_STRING
));
```

### 读取配置

定义了配置文件之后，统一使用系统提供的Config方法来读取已有的配置。

获取已经设置的参数值：Config::get('参数名称')
设置自定义配置值：Config::set('参数名称','参数值')

因为配置参数是全局有效的，因此本方法可以在任何地方读取任何配置，即使某个设置参数已经生效过期了。

---

## 路由

### 使用须知

使用前确保已打开服务器的REWRITE功能，不然会404。

### 服务器配置

Apache配置：

```
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$ index.php [L,E=PATH_INFO:$1]
</IfModule>
```
Nginx配置：

```
    待更新
```

### URL格式

入口文件是应用的单一入口，对应用的所有请求都定向到应用入口文件，系统会从URL参数中解析当前请求的模块、控制器和操作，以下带有(?)表示的是QUERY_STRING模式，不带(?)则是PATH_INFO模式，默认采用的是QUERT_STRING模式，保证最高兼容性。

> http://example.com/?控制器/操作(/参数值)  QUERY_STRING模式
> http://example.com/控制器/操作(参数值)   PATH_INFO模式

**本文档以下默认采用PATH_INFO模式进行解释，不再阐述QUERY_STRING模式。**

如果我们直接访问入口文件的话，由于URL中没有控制器和操作，因此系统会访问默认控制器（Index）的默认操作（index），因此下面的访问是等效的：

> http://example.com/  
> http://example.com/Index/index  

其中，若有参数，那么参数将自动转化成$_GET变量：

> http://example.com/Index/index/1 $_GET['xx']=1

不过，依然可以获取到普通形式的$_GET变量：

> http://example.com/Index/Login?var=value $_GET['var'] 依然有效  

---

## 控制器

### 什么是控制器？

**简而言之，一个控制器就是一个类文件，是以一种能够和 URI 关联在一起的方式来命名的。**

考虑下面的 URI:

```
http://example.com/Blog/
```
上例中，KotoriFramework 将会尝试查询一个名为 BlogController.php 的控制器并加载它。

**当控制器的名称和 URI 的第一段匹配上时，它将会被加载。**

### 让我们试试看：Hello World！
接下来你会看到如何创建一个简单的控制器，打开你的文本编辑器，新建一个文件 Blog.php ， 然后放入以下代码:

```php
class BlogController extends Controller {

    public function index()
    {
        echo 'Hello World!';
    }
}
```
然后将文件保存到 App/Controller 目录下。

现在使用类似下面的 URL 来访问你的站点:

http://example.com/Blog/

如果一切正常，你将看到：

Hello World!

另外，一定要确保你的控制器继承了父控制器类，这样它才能使用父类的方法。

方法
上例中，方法名为 index() 。"index" 方法总是在 URI 的 第二段 为空时被调用。 另一种显示 "Hello World" 消息的方法是:

example.com/index.php/blog/index/
URI 中的第二段用于决定调用控制器中的哪个方法。

让我们试一下，向你的控制器添加一个新的方法:

```php
class BlogController extends Controller {

    public function index()
    {
        echo 'Hello World!';
    }

    public function comments()
    {
        echo 'Look at this!';
    }
}
```
现在，通过下面的 URL 来调用 comments 方法:
```
http://example.com/Blog/comments/
```
你应该能看到你的新消息了。

### 通过 URI 分段向你的方法传递参数

如果你的 URI 多于两个段，多余的段将作为参数传递到你的方法中。

例如，假设你的 URI 是这样:
```
http://example.com/Products/shoes/sandals/123
```
你的方法将会收到第三段和第四段两个参数（"sandals" 和 "123"）:

```php
class ProductsController extends Controller {

    public function shoes($sandals, $id)
    {
        echo $sandals;
        echo $id;
    }
}
```

> **重要**
> 如果你使用了 URI 路由 ，传递到你的方法的参数将是路由后的参数。



### Url生成

为了配合URL格式，我们需要能够动态的根据当前的URL设置生成对应的URL地址，为此，内置提供了url方法，用于URL的动态生成，可以确保项目在移植过程中不受环境的影响。

示例如下：

```php
echo Route::url('Index/show',array('id'=>1));
//即http://example.com/Index/show/id/1
echo Route::url('Blog/index');
//如果不需要传GET变量，只需写第一个参数
```

### AJAX返回

用于返回JSON格式的数据。

调用示例：

```php
$array['name'] = 'MahuaTeng';
$array['length'] = 1;
Response::throwJson($array);
```

### 跳转

301/302跳转到某页面

调用示例：

```php
Response::redirect('http://www.qq.com',true);//跳转到马化腾首页
//根据第二个参数来判断是301还是302跳转
```

### 接受安全的变量

在Web开发过程中，我们经常需要获取系统变量或者用户提交的数据，这些变量数据错综复杂，而且一不小心就容易引起安全隐患，但是如果利用好框架P提供的变量获取功能，就可以轻松的获取和驾驭变量了。

示例：

```php
    $id    =  Request::input('get.id'); // 获取get变量
    $name  =  Request::input('post.name');  // 获取post变量
    $value =  Request::input('session.var'); // 获取session变量
    $name  =  Request::input('cookie.name'); //获取cookie变量
    $file  =  Request::input('server.PHP_SELF'); // 获取server变量
    $get   =  Request::input('get.') //获取$_GET数组
```

### 控制器间相互调用

比如BlogController中：
```php
   public function show()
   {
       echo 'test';
   }
```

接下来我们在IndexController中调用它：
```php
   public function index()
   {
       Route::controller('Blog')->show();
   }
```


### 错误页面

当你的系统发生错误时，将输出错误页面。

---

## 视图

视图引擎采用原生PHP的方式，并不是用模板语言。

通过Controller类的assign来给模板变量赋值，通过display方法来渲染模板。

assign方法接受两个参数，第一个参数是模板变量名，第二个参数是模板变量值。

display方法可以接受1个或0个参数。当没有参数时，则默认使用View/控制器名/Action名.php作为模板；如果参数值不带有'/'，则默认使用View/控制器名/参数值.html作为模板；如果参数值带有1个'/'，则会使用View/参数值.html作为模板。

示例：

```php
class IndexController extends Controller { 
    public function indexAction(){
        $this->assign('var', 'hello Kotori'); //给模板变量赋值
        $this->display();           //使用View/Index/index.html作为模板
    }
    public function TestAction(){
        $this->display('fuck/test');    //使用View/fuck/test.html作为模板
    }
}
```

模板include功能，通过N来引入其他模板，该静态方法接受1个或2个参数，第一个参数是模板，规则与display的参数相同，第二个参数是传递给该模板的模板变量，必须是关联型数组。

比如有一个公共header文件位于View/Public/header.html，下面进行引入
```php
<!DOCTYPE html>
<head>
    <title><?php echo $title;?></title>
</head>
```
当前模板如下引入
```php
$data = array(
        'title' => 'Welcome',  //设置title变量为Welcome
        );
View::need('Public/header', $data); ?>
```

---

## 路由

一般情况下，一个 URL 字符串和它对应的控制器中类和方法是一一对应的关系。 URL 中的每一段通常遵循下面的规则:

http://example.com/class/function/id/
但是有时候，你可能想改变这种映射关系，调用一个不同的类和方法，而不是 URL 中对应的那样。

例如，假设你希望你的 URL 变成下面这样:
```
http://example.com/product/1/
http://example.com/product/2/
http://example.com/product/3/
http://example.com/product/4/
```

URL 的第二段通常表示方法的名称，但在上面的例子中，第二段是一个商品 ID ， 为了实现这一点，KotoriFramework 允许你重新定义 URL 的处理流程。


### 设置你自己的路由规则

在入口文件中设置URL_ROUTE数组：
```
'URL_ROUTE' => array(
  'product/([0-9])'=>'catalog/product_lookup',
);
```

在一个路由规则中，数组的键表示要匹配的 URI ，而数组的值表示要重定向的位置。 上面的例子中，如果 URL 的第一段是字符串 "product" ，第二段是个数字，那么， 将调用 "catalog" 类的 "product_lookup" 方法。

### 例子

这里是一些路由的例子:

```
'journals' => 'blogs',
```
URL 的第一段是单词 "journals" 时，将重定向到 "blogs" 类。

```
'blog/joe' => 'blogs/users/34',
```
URL 包含 blog/joe 的话，将重定向到 "blogs" 类和 "users" 方法。ID 参数设为 "34" 。

```
'product/([^/]+)'] => 'catalog/product_lookup',
```
URL 的第一段是 "product" ，第二段是任意字符时，将重定向到 "catalog" 类的 "product_lookup" 方法。

```
'product/(([0-9])' => 'catalog/product_lookup_by_id/$1',
```
URL 的第一段是 "product" ，第二段是数字时，将重定向到 "catalog" 类的 "product_lookup_by_id" 方法，并将第二段的数字作为参数传递给它。

### 回调函数

如果你正在使用的 PHP 版本高于或等于 5.3 ，你还可以在路由规则中使用回调函数来处理逆向引用。 例如:
```
'products/([a-zA-Z]+)/edit/(\d+)' => function ($product_type, $id)
{
    return 'catalog/product_edit/' . strtolower($product_type) . '/' . $id;
},

---

## 系统常量

CONTROLLER_NAME 当前控制器名
ACTION_NAME 当前操作名
PUBLIC_DIR Public路径
Request::isPost() 是否POST方式
Request::isGet() 是否GET方式
Request::isAjax() 是否AJAX方式

---

## 数据库操作

Kotori Framework不能称为一个MVC框架的原因就是没有M层，然而，小项目写M层有Kotori用？不服来咬我啊~

数据库类直接采用Medoo。

[详细文档请点击](http://medoo.in/doc)

在入口文件中配置好有关数据库的几个常量后，即可以单例模式调用数据库层，例如：

```php
// 查询所有字段
$datas = $this->db->query("SELECT * FROM users");
print_r($datas);
```

---


---

## 高级功能

### 自动加载
除了自动加载Controller类外，KotoriFramework还会自动加载一个App/common.php和App/Lib下的类文件

App/common.php这个文件里可以是通用的函数，比如说时间转换，自定义过滤字符串等方法，如果不存在也不会影响程序的运行。

当KotoriFramework遇到一个未定义的类时，会尝试去加载App/Lib/类名.class.php。比如：

```php
//App/Lib/Page.class.php
class Page{}
```
然后在控制器中直接加载即可。如果文件不存在，或者文件中也没有定义这个类，将会报错。

### 日志记录

KotoriFramework提供了一个简单的日志类，可以分级记录各类信息，目前提供了Normal和Sql 2种级别。日志会存储在App/Log目录下，当然前提条件是该目录是可写的。日志是按天存储的。

---


再一次感谢您花费时间阅读这份说明文档！