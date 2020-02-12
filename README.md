# Voyager Model Log

  Install hook
  
    php artisan hook:install model-log

  Enable hook
  
    php artisan hook:enable model-log

  Publishing config
  
    php artisan vendor:publish --provider="Jahondust\ModelLog\ModelLogServiceProvider"


#### How to use Model logging

Voyager Model Log is used to observe any changes in the model via writing it in the "model-log" 
table in your database.You can use it, simply adding  "use ModelLogging;" in your model.

**Example:**
    
	<?php
	namespace App;

	use Illuminate\Database\Eloquent\Model;
	use Jahondust\ModelLog\Traits\ModelLogging;

	class Post extends Model
	{
		use ModelLogging;
	}
	
	

If you want to exclude or include the changes in the model's field, 
just remove or add the field from model's  "$logFields" property 

	
**Example:**
    
	$logFields = ['title'];
		
	
	
If you want to differently display  "user" field in the "model-log" table, add "getLogNameAttribute"  property to your User model.
	
**Example:**

    public function getLogNameAttribute()
    {
       return $this->firstname . " " . $this->lastname;
    }
	
	
	
![Voyager model log](https://i.imgur.com/8Nr3vIx.png)
