<?php

namespace App\Providers;

use App\Models\Group;
use App\Models\Bill;
use App\Models\GroupMember;
use App\Models\Settle;
use App\Policies\GroupPolicy;
use App\Policies\BillPolicy;
use App\Policies\GroupMemberPolicy;
use App\Policies\SettlePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Group::class => GroupPolicy::class,
        Bill::class => BillPolicy::class,
        GroupMember::class => GroupMemberPolicy::class,
        Settle::class => SettlePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
