<?php

namespace Pocket\Phquery;

use Closure;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Laravel\Dusk\Chrome\SupportsChrome;
use Laravel\Dusk\Concerns\ProvidesBrowser;
use Throwable;

class Chrome
{
    use ProvidesBrowser, SupportsChrome {
        ProvidesBrowser::browse as protected duskBrowse;
    }

    /**
     * Chrome singleton
     *
     * @var Chrome|null
     */
    private static ?Chrome $instance = null;

    /**
     * Start the Chromedriver process and return Chrome singleton.
     *
     * @return static
     */
    public static function instance(): Chrome
    {
        if (!isset(self::$instance)) {
            static::startChromeDriver();
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Create browser instances.
     *
     * @param Closure $callback function (\Pocket\Phquery\Browser $first, \Pocket\Phquery\Browser $second, ...) {}
     * @return mixed|void Returns the return value of the callback closure.
     * @throws Throwable
     */
    public static function browse(Closure $callback)
    {
        $browsers = collect(static::instance()->createBrowsersFor($callback));

        try {
            return $callback(...$browsers->all());
        } catch (Throwable $e) {
            static::instance()->captureFailuresFor($browsers);
            static::instance()->storeSourceLogsFor($browsers);

            throw $e;
        } finally {
            static::instance()->storeConsoleLogsFor($browsers);

            static::$browsers = static::instance()->closeAllButPrimary($browsers);
        }
    }

    /**
     * @inheritDoc
     */
    protected function getCallerName(): string
    {
        return str_replace('\\', '_', get_class($this));
    }

    /**
     * Create the RemoteWebDriver instance.
     *
     * @return RemoteWebDriver
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
        ])->unless($this->hasHeadlessDisabled(), function ($items) {
            return $items->merge([
                '--disable-gpu',
                '--headless',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['PHQUERY_DRIVER_URL'] ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * @inheritDoc
     */
    protected function newBrowser(RemoteWebDriver $driver): Browser
    {
        return new Browser($driver);
    }

    /**
     * Determine whether the browser has disabled headless mode.
     *
     * @return bool
     */
    protected function hasHeadlessDisabled(): bool
    {
        return ($_SERVER['PHQUERY_HEADLESS_DISABLED'] ?? false) ||
            ($_ENV['PHQUERY_HEADLESS_DISABLED'] ?? false);
    }

    /**
     * Determine if the browser window should start maximized.
     *
     * @return bool
     */
    protected function shouldStartMaximized(): bool
    {
        return ($_SERVER['PHQUERY_START_MAXIMIZED'] ?? false) ||
            ($_ENV['PHQUERY_START_MAXIMIZED'] ?? false);
    }
}