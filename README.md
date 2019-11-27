# Voyager Model Log

  Install hook
  
    php artisan hook:install model-log

  Enable hook
  
    php artisan hook:enable model-log

  Publishing config
  
    php artisan vendor:publish --provider="Jahondust\ModelLog\ModelLogServiceProvider"


#### How to use Model logging

**Example:**
    
    <?php
    namespace App;

    use Illuminate\Database\Eloquent\Model;
    use Jahondust\ModelLog\Traits\ModelLogging;

    class Post extends Model
    {
        use ModelLogging;
    }
    
![Voyager model log](https://i.imgur.com/7bS5mAJ.png)
