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

PHP5.3以上版本（支持PHP7）

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
* 养成使用I函数获取输入变量的好习惯；

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

```php
require './Kotori.dll';//正式版
//require './Kotori.class.php';//开发版
$config = array(
    'APP_PATH'    => './App/',       #APP代码文件夹
    'DB_HOST'     => 'localhost',    #数据库主机地址
    'DB_PORT'     => '3306',         #数据库端口，默认为3306
    'DB_USER'     => 'root',         #数据库用户名
    'DB_PWD'      => 'root',         #数据库密码
    'DB_NAME'     => 'test',         #数据库名
    'USE_SESSION' => true,           #是否开启session，默认false
);
Kotori::getInstance($config)->run();
```

### 读取配置

定义了配置文件之后，统一使用系统提供的C方法（可以借助Config单词来帮助记忆）来读取已有的配置。

获取已经设置的参数值：C('参数名称')

C方法也可以用于读取二维配置：

```php
    //获取用户配置中的用户类型设置
    C('USER_CONFIG.USER_TYPE');
```

因为配置参数是全局有效的，因此C方法可以在任何地方读取任何配置，即使某个设置参数已经生效过期了。

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
    #去掉$是为了不匹配行末，即可以匹配.php/，以实现pathinfo
    #如果你不需要用到php5后缀，也可以将其去掉
    location ~ .php
        {
                #原有代码
                
                #定义变量 $path_info ，用于存放pathinfo信息
                set $path_info "";
                #定义变量 $real_script_name，用于存放真实地址
                set $real_script_name $fastcgi_script_name;
                #如果地址与引号内的正则表达式匹配
                if ($fastcgi_script_name ~ "^(.+?\.php)(/.+)$") {
                        #将文件地址赋值给变量 $real_script_name
                        set $real_script_name $1;
                        #将文件地址后的参数赋值给变量 $path_info
                        set $path_info $2;
                }
                #配置fastcgi的一些参数
                fastcgi_param SCRIPT_FILENAME $document_root$real_script_name;
                fastcgi_param SCRIPT_NAME $real_script_name;
                fastcgi_param PATH_INFO $path_info;
        }
```

### URL格式

入口文件是应用的单一入口，对应用的所有请求都定向到应用入口文件，系统会从URL参数中解析当前请求的模块、控制器和操作：

> http://localhost/控制器/操作(/参数名/参数值)

如果我们直接访问入口文件的话，由于URL中没有控制器和操作，因此系统会访问默认控制器（Index）的默认操作（index），因此下面的访问是等效的：

> http://serverName/  
> http://serverName/Index/index  

其中，若有参数，那么参数将自动转化成$_GET变量：

> http://serverName/Index/index/id/1 $_GET['id']=1

不过，依然可以获取到普通形式的$_GET变量：

> http://localhost/index.php/Index/Login?var=value $_GET['var'] 依然有效  

---

## 控制器

### 控制器定义

一般来说，控制器是一个类，而操作则是控制器类的一个公共方法。

下面就是一个典型的控制器类的定义：

```php
class IndexController extends Controller
{
    protected function _init()
    {
    //相当于__construct()方法
    }

    public function index()
    {
        echo 'Hello Kotori';
    }

    public function form()
    {
        echo $_GET['testword'];
    }

}

```

所有的控制器必须继承Controller类或其子类，并且类名必须以Controller结尾，统一放置在Controller目录下，文件名必须是“类名.class.php”。

每一个Action对应控制器类的一个方法，方法名建议按开发规范来命名，同时必须是public权限，不然URL无法访问到。

### Action参数绑定

参数绑定是通过直接绑定URL地址中的变量作为操作方法的参数，可以简化方法的定义。

例如，我们给Blog控制器定义了两个操作方法read和archive方法，由于read操作需要指定一个id参数，archive方法需要指定年份（year）和月份（month）两个参数，那么我们可以如下定义：

```php
    class BlogController extends Controller{
        public function read($id){
            echo 'id='.$id;
        }
        public function archive($year='2013',$month='01'){
            echo 'year='.$year.'&month='.$month;
        }
    }
```

URL的访问地址分别是：

> http://serverName/Blog/read/id/5  
> http://serverName/Blog/archive/year/2013/month/11  

两个URL地址中的id参数和year和month参数会自动和read操作方法以及archive操作方法的同名参数绑定。

按照变量名进行参数绑定的参数必须和URL中传入的变量名称一致，但是参数顺序不需要一致。也就是说

> http://serverName/Blog/archive/month/11/year/2013  

和上面的访问结果是一致的，URL中的参数顺序和操作方法中的参数顺序都可以随意调整，关键是确保参数名称一致即可。

如果用户访问的URL地址是（至于为什么会这么访问暂且不提）：

> http://serverName/Blog/read/  

那么会抛出异常，报错的原因很简单，因为在执行read操作方法的时候，id参数是必须传入参数的，但是方法无法从URL地址中获取正确的id参数信息。由于我们不能相信用户的任何输入，因此建议你给read方法的id参数添加默认值，例如：

```php
public function read($id=0){
        echo 'id='.$id;
    }
```

### Url生成

为了配合URL格式，我们需要能够动态的根据当前的URL设置生成对应的URL地址，为此，内置提供了url方法，用于URL的动态生成，可以确保项目在移植过程中不受环境的影响。

示例如下：

```php
$this->redirect(U('Index/show',array('id'=>1)));
//控制器中跳转到Index控制器下的show方法，
//即http://localhost/Index/show?id=1
echo U('Blog/index');
//如果不需要传GET变量，只需写第一个参数
```

### AJAX返回

用于返回JSON格式的数据。

调用示例：

```php
$array['name'] = 'MahuaTeng';
$array['length'] = 1;
$this->ajaxReturn($array);
```

### 跳转

301跳转到某页面

调用示例：

```php
$this->redirect('http://www.qq.com');//跳转到马化腾首页
```

### 接受安全的变量

在Web开发过程中，我们经常需要获取系统变量或者用户提交的数据，这些变量数据错综复杂，而且一不小心就容易引起安全隐患，但是如果利用好框架P提供的变量获取功能，就可以轻松的获取和驾驭变量了。

示例：

```php
    $id    =  I('get.id'); // 获取get变量
    $name  =  I('post.name');  // 获取post变量
    $value =  I('session.var'); // 获取session变量
    $name  =  I('cookie.name'); //获取cookie变量
    $file  =  I('server.PHP_SELF'); // 获取server变量
    $get   =  I('get.') //获取$_GET数组
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
       A('Blog')->show();
   }
```

---

## 数据库操作

Kotori Framework不能称为一个MVC框架的原因就是没有M层，然而，小项目写M层有Kotori用？不服来咬我啊~

数据库类直接采用某个修改版PDO类。

[详细文档请点击](https://github.com/kokororin/PHP-PDO-MySQL-Class)

在入口文件中配置好有关数据库的几个常量后，即可以单例模式调用数据库层，例如：

```php
// 查询所有字段
$datas = M()->query("SELECT * FROM users");
print_r($datas);
```

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

比如有一个公共header文件位于View/public/header.html，下面进行引入
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
N('public/header', $data); ?>
```

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

## 更新日志

* 2015/8/1 整体框架完成  
* 2015/8/2 将数据库操作类改为medoo  
* 2015/8/3 修复PATHINFO致命BUG  
* 2015/8/5 加入错误提示功能
* 2015/8/9 增加A方法，修复部分小bug

---

再一次感谢您花费时间阅读这份说明文档！