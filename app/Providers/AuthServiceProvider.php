<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Passport::routes();

        try {
            Passport::tokensExpireIn(now()->addMonths(env('TOKENS_EXPIRE', 24)));
            Passport::refreshTokensExpireIn(now()->addmonths(env('TOKENS_REFRESH_EXPIRE', 24)));
            Passport::personalAccessTokensExpireIn(now()->addMonths(env('PERSONAL_TOKENS_EXPIRE', 60)));
        } catch (\Exception $e) {

        }
    }
}
