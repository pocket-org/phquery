<?php

namespace Procket\Phquery;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverAlert;
use Facebook\WebDriver\WebDriverElement;
use Facebook\WebDriver\WebDriverTargetLocator;
use Illuminate\Support\Collection;
use Laravel\Dusk\Browser as DuskBrowser;
use Laravel\Dusk\ElementResolver;

class Browser extends DuskBrowser
{
    /**
     * @inheritDoc
     */
    public function __construct($driver, $resolver = null)
    {
        $resolver = $resolver ?: new ElementResolver($driver, '');

        parent::__construct($driver, $resolver);
    }

    /**
     * Creates a new browser tab and switches the focus to the new window.
     *
     * @return $this
     */
    public function newTab(): Browser
    {
        $driver = $this->driver->switchTo()->newWindow(WebDriverTargetLocator::WINDOW_TYPE_TAB);

        return new static($driver);
    }

    /**
     * Creates a new browser window and switches the focus to the new window.
     *
     * @return $this
     */
    public function newWindow(): Browser
    {
        $driver = $this->driver->switchTo()->newWindow(WebDriverTargetLocator::WINDOW_TYPE_WINDOW);

        return new static($driver);
    }

    /**
     * Return an opaque handle to this window that uniquely identifies it within this driver instance.
     *
     * @return string|null
     */
    public function getWindowHandle(): ?string
    {
        return $this->driver->getWindowHandle();
    }

    /**
     * Get all window handles available to the current session.
     *
     * @return Collection
     */
    public function getWindowHandles(): Collection
    {
        return collect($this->driver->getWindowHandles());
    }

    /**
     * Switch the focus to another window by its handle.
     *
     * @param string $handle
     * @return $this
     */
    public function switchToWindow(string $handle): Browser
    {
        $driver = $this->driver->switchTo()->window($handle);

        return new static($driver);
    }

    /**
     * Switch the focus to another window by its index.
     *
     * @param int|string $index
     * @return $this
     */
    public function switchToWindowByIndex(int|string $index): Browser
    {
        $handle = $this->getWindowHandles()->slice((int)$index, 1)->first();

        return $this->switchToWindow($handle);
    }

    /**
     * Switch to the first window.
     *
     * @return $this
     */
    public function switchToFirstWindow(): Browser
    {
        return $this->switchToWindow($this->getWindowHandles()->first());
    }

    /**
     * Switch to the last window.
     *
     * @return $this
     */
    public function switchToLastWindow(): Browser
    {
        return $this->switchToWindow($this->getWindowHandles()->last());
    }

    /**
     * Switch to the iframe by its id or name.
     *
     * @param int|string|WebDriverElement|null $frame
     * @return $this
     */
    public function switchToFrame(int|string|WebDriverElement|null $frame): Browser
    {
        $driver = $this->driver->switchTo()->frame($frame);

        return new static($driver);
    }

    /**
     * Switch to the parent iframe.
     *
     * @return $this
     */
    public function switchToParent(): Browser
    {
        $driver = $this->driver->switchTo()->parent();

        return new static($driver);
    }

    /**
     * Switch to the top window.
     *
     * @return $this
     */
    public function switchToTop(): Browser
    {
        $driver = $this->driver->switchTo()->defaultContent();

        return new static($driver);
    }

    /**
     * Switches to the element that currently has focus within the document currently “switched to”,
     * or the body element if this cannot be detected.
     *
     * @return RemoteWebElement
     */
    public function switchToActiveElement(): RemoteWebElement
    {
        return $this->driver->switchTo()->activeElement();
    }

    /**
     * Switch to the currently active modal dialog for this particular driver instance.
     *
     * @return WebDriverAlert
     */
    public function switchToAlert(): WebDriverAlert
    {
        return $this->driver->switchTo()->alert();
    }
}