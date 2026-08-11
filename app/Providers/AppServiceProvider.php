<?php

namespace App\Providers;

use App\Services\Payments\PaymentGatewayInterface;
use App\Services\Payments\StripeGateway;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PaymentGatewayInterface::class, StripeGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
    }

    /**
     * Public API rate limit — 60 req/min per IP, per
     * docs/architecture/api-architecture.md §4. There is no authenticated
     * tier yet since V1 has no token-based API consumers.
     *
     * Anonymous public forms (Contact, Newsletter, Volunteer, Partnership)
     * share one limiter — 3 submissions per IP per hour, matching saba.md
     * §23.2's contact-form figure exactly rather than inventing separate
     * numbers for the other three forms.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        RateLimiter::for('public-forms', function (Request $request) {
            return Limit::perHour(3)->by($request->ip());
        });

        // saba.md §8.3 — donation-initiation rate limit, not applied to the
        // Stripe webhook (that's signature-verified instead, see
        // docs/architecture/payment-architecture.md §5).
        RateLimiter::for('donations', function (Request $request) {
            return Limit::perHour(5)->by($request->ip());
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
