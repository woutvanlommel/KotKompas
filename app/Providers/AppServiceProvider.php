<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(HtmlSanitizer::class, function () {
            $config = (new HtmlSanitizerConfig)
                ->allowSafeElements();

            return new HtmlSanitizer($config);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('richtext', function (string $expression): string {
            return "<?php echo app(\Symfony\Component\HtmlSanitizer\HtmlSanitizer::class)->sanitize($expression); ?>";
        });
    }
}
