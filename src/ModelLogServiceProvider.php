<?php

namespace Jahondust\ModelLog;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Schema;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Jahondust\ModelLog\Models\ModelLog;
use Jahondust\ModelLog\Policies\ModelLogPolicy;
use TCG\Voyager\Models\Menu;
use TCG\Voyager\Models\MenuItem;
use TCG\Voyager\Models\Permission;
use TCG\Voyager\Models\Role;

Class ModelLogServiceProvider extends ServiceProvider
{
    private $models = [
        'ModelLog',
    ];

    private $permissions = [
        'browse_model_log',
        'clear_model_log'
    ];

    protected $policies = [
        ModelLog::class => ModelLogPolicy::class
    ];

    public function boot(){
        try{
            $this->registerPolicies();

            $this->loadViewsFrom(__DIR__.'/../resources/views', 'modellog');
            $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'modellog');

            $this->loadModels();

        } catch(\Exception $e){
            return $e->getMessage();
        }
    }

    public function register()
    {
        // Create Routes
        app(Dispatcher::class)->listen('voyager.admin.routing', function ($router) {
            $this->addRoutes($router);
        });

        // Create Menu
        app(Dispatcher::class)->listen('voyager.menu.display', function ($menu) {
            $this->addThemeMenuItem($menu);
        });

        //Load helpers
        $this->loadHelpers();

        // Publish config
        $this->publishes([dirname(__DIR__).'/config/model-log.php' => config_path('model-log.php')], 'model-log-config');

        // Create Table
        $this->addLogsTable();
    }

    public function addRoutes($router){
        $namespacePrefix = '\\Jahondust\\ModelLog\\Controllers\\';
        $router->get('model_log', ['uses' => $namespacePrefix.'ModelLogController@browse', 'as' => 'model_log.index']);
        $router->delete('model_log_clear', ['uses' => $namespacePrefix.'ModelLogController@clear', 'as' => 'model_log.clear']);
        $router->get('modal_log_assets', ['uses' => $namespacePrefix.'ModelLogController@assets', 'as' => 'model_log.assets']);

    }

    /**
     * Adds the Model Logs icon to the admin menu.
     *
     * @param TCG\Voyager\Models\Menu $menu
     */
    public function addThemeMenuItem(Menu $menu)
    {
        if ($menu->name == 'admin') {
            $menuItem = $menu->items->where('route', 'voyager.model_log.index')->first();
            if (is_null($menuItem)) {
                $menu->items->add(MenuItem::create([
                    'menu_id' => $menu->id,
                    'url' => '',
                    'route' => 'voyager.model_log.index',
                    'title' => 'Model Logs',
                    'target' => '_self',
                    'icon_class' => 'voyager-logbook',
                    'color' => null,
                    'parent_id' => null,
                    'order' => 98,
                ]));
                $this->ensurePermissionExist();
                return redirect()->back();
            }
        }
    }

    /**
     * Include models for Model Logs.
     *
     * @return none
     */
    private function loadModels(){
        foreach($this->models as $model){
            $namespacePrefix = 'Jahondust\\ModelLog\\Models\\';
            if(!class_exists($namespacePrefix . $model)){
                @include(__DIR__.'/Models/' . $model . '.php');
            }
        }
    }
    /**
     * Load helpers.
     */
    protected function loadHelpers()
    {
        foreach (glob(__DIR__.'/Helpers/*.php') as $filename) {
            require_once $filename;
        }
    }

    /**
     * Add Permissions for Model Logs if they do not exist yet.
     *
     * @return none
     */
    protected function ensurePermissionExist()
    {
        foreach ($this->permissions as $permissionName) {
            $permission = Permission::firstOrNew(['key' => $permissionName, 'table_name' => 'model_log']);
            if (!$permission->exists) {
                $permission->save();
                $role = Role::where('name', 'admin')->first();
                if (!is_null($role)) {
                    $role->permissions()->attach($permission);
                }
            }
        }
    }

    /**
     * Add the necessary Model Logs table if they do not exist.
     *
     * @return none
     */
    private function addLogsTable(){
        if(!Schema::hasTable('model_log')){
            Schema::create('model_log', function (Blueprint $table) {
                $table->increments('id');
                $table->string('table_name');
                $table->bigInteger('row_id')->unsigned()->index();
                $table->string('event')->index();
                $table->text('before')->nullable();
                $table->text('after')->nullable();
                $table->string('ip_address')->nullable();
                $table->text('user_agent')->nullable();
                $table->bigInteger('user_id')->unsigned()->index();
                $table->timestamps();
            });
        }
    }
}
