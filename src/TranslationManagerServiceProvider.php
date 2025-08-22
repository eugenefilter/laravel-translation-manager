<?php

namespace Gemini\LaravelTranslationManager;

use Illuminate\Support\ServiceProvider;

class TranslationManagerServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 * @return void
	 */
	public function boot(): void
	{
		$this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
		$this->loadViewsFrom(__DIR__ . '/../resources/views', 'translation-manager');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/translation-manager.php' => config_path('translation-manager.php'),
            ], 'config');
        }
	}

	/**
	 * Register any application services.
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(
            __DIR__.'/../config/translation-manager.php', 'translation-manager'
        );
	}
}
