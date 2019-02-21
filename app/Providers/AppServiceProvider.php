<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Monolog\Logger;
use Yansongda\Pay\Pay;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
       Schema::defaultStringLength(191);
       $this->app->singleton('alipay',function(){
            $config=config('pay.alipay');

            if (app()->environment()!=='production') {
                $config['mode']='dev';
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log']['level'] = Logger::WARNING;
            }
            $config['notify_url']='http://requestbin.fullcontact.com/18ii3tu1';//route('payment.alipay.notify');
            $config['return_url']=route('payment.alipay.return');
            return Pay::alipay($config);
       });

       $this->app->singleton('wechat_pay',function(){
            $config=config('pay.wechat_pay');

            if (app()->environment()!=='production') {
                $config['log']['level'] = Logger::DEBUG;
            }else{
                $config['log']['level'] = Logger::WARNING;
            }

            return Pay::wechat($config);
       });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
