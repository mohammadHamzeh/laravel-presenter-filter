## Presenter-Filter Laravel
### Filter
This package allows you to easily handle database filtering through query strings.
example: /users?status=1&name='kami'
### Presenter
Sometimes our models get too fat, and that can make our development a lot harder.
In this case, we use a second class that has the same function as the model and is used as a second model, and the Harrow method can be included in this class.

## Installation
you can install the package via composer:
```bash
composer require mohammad-hamzeh/presenter-filter
```

You Must by publishing configuration by issuing following artisan command ```php artisan vendor:publish```.
    
## Introduction
The package allows you to create two types of classes: filter class and presenter class

## Usage
You have access to two commands and you can use them to create your own filter and presenter classes
#### make:filter command
You can use the following command to create a new filter.

```php artisan make:filter UserFilter```

This will create a new filter in the app/Filters directory.

options:

1-You can add the model to the command

‍‍‍‍‍‍```php artisan make:filter UserFilter --model=User```

Used by default Models folder If you have saved models elsewhere, change the config Modules of this folder

#### make:presenter Command
You can use the following command to create a new Presenter

```php artisan make:presenter UserPresenter```

options:

1- You can add the model to command

```php artisan make:presenter UserPresenter --model=User```

## Example With Filter
Let's say you want to use filterable on User model. You will have to create the filter class App/Filters/PostFilter.php (```php artisan make:filter PostFilter --model=Post```)

If you use the --model option, filterable will be added directly to the model

```php
<?php
namespace App\Filters;

use mhamzeh\packageFp\Filters\contracts\QueryFilter;

class UserFilter extends QueryFilter
{
    public function name($value){
        return $this->builder->where('name','LIKE',"%$value%");        
    }
}
```

Now you need to add local scope to your model if you have not used the --model option:
```php
use mhamzeh\packageFp\Filters\contracts\Filterable;
...
class User extends Model
{
    use Filterable;
    ...
}
```

Finaly, call the scope in controller like so:

```php
use App\Filters\UserFilter;
...
public function index()
{
    return User::filters(new UserFilter())->paginate();
}
```


## Example With Presenter
Let's say you want to use Presentable And introduce the presenter class on User model. You will have to create the filter class App/Presenter/UserPresenter.php (```php artisan make:presenter UserPresenter --model=User```)

If you use the --model option, Presentable and presenter class will be added directly to the model

```php
<?php
namespace App\Presenter;

use mhamzeh\packageFp\Presenter\Contracts\Presenter;

class UserPresenter extends Presenter
{
    public function full_name(){
         return $this->entity->name ." ".$this->entity->full_name;        
    }
}
```
Now you need to add local scope to your model if you have not used the --model option:
```php
use mhamzeh\packageFp\Presenter\Contracts\Presentable;
...
class User extends Model
{
    use Presentable;
    protected $presenter = UserPresenter::class;

    ...
}
```
Finally you can use this method in your [Blade](https://laravel.com/docs/8.x/blade) or [Api Resources](https://laravel.com/docs/8.x/eloquent-resources) or Controller
for example [Blade](https://laravel.com/docs/8.x/blade):
```php
 @foreach($users as $user)
     <div>
          <p>fullname : $user->present()->full_name </p>
      </div>
 @endforeach
```
in [Api Resources](https://laravel.com/docs/8.x/eloquent-resources):
```php
class UserResource extends JsonResource
{
   /**
    * Transform the resource into an array.
    *
    * @param \Illuminate\Http\Request $request
    * @return array
    */
   public function toArray($request)
   {
       return [
            'id'=>$this->id,
            'full_name'=> $this->present()->full_name,
            'email'=>$this->email
        ];
   }
}
```



  


