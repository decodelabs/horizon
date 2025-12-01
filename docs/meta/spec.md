# Horizon — Package Specification

> **Cluster:** `frontend`
> **Language:** `php`
> **Milestone:** `m4`
> **Repo:** `https://github.com/decodelabs/horizon`
> **Role:** HTML document components

This document describes the purpose, contracts, and design of **Horizon** within the Decode Labs ecosystem.

It is aimed at:

- Developers **using** Horizon in their own applications or libraries.
- Contributors **maintaining or extending** Horizon.
- Tools and AI assistants that need to reason about its behaviour.

---

## 1. Overview

### 1.1 Purpose

Horizon provides a simple structure for building and rendering HTML views using the `DecodeLabs\Tagged` interfaces. It offers a `Page` class that encapsulates the complete HTML document structure (head and body sections) with support for metadata management, script and stylesheet inclusion, decorator patterns, and integration with Harvest (PSR-15) and Greenleaf (routing) for seamless HTTP response generation. Horizon enables programmatic construction of HTML pages with a clean, object-oriented API while maintaining compatibility with Tagged's markup generation system.

### 1.2 Non-Goals

Horizon does **not**:

- Provide a full templating engine (see other packages for template rendering)
- Handle routing or URL generation (see `decodelabs/greenleaf` for routing)
- Manage HTTP request/response lifecycle directly (uses Harvest transformers)
- Provide CSS or JavaScript frameworks
- Handle form processing or validation
- Manage application state or session handling

---

## 2. Role in the Ecosystem

### 2.1 Cluster & Positioning

- **Cluster:** `frontend` (see Chorus taxonomy)
- Horizon is a mid-level frontend package that builds on Tagged (markup generation) and integrates with Harvest (HTTP) and Greenleaf (routing). It provides the document structure layer, sitting above Tagged's markup primitives and below application-specific view logic.

### 2.2 Typical Usage Contexts

Typical places Horizon appears:

- Web application view rendering
- HTML page generation in HTTP responses
- Greenleaf route actions that return Page instances
- Decorator-based page customization
- Fragment-based page composition

Horizon is intended to be used whenever you need to programmatically construct complete HTML documents with proper head/body structure, metadata management, and integration with the Decode Labs HTTP stack.

---

## 3. Public Surface

> This section focuses on the conceptual API, not every symbol.

### 3.1 Key Types

The primary public types are:

- `DecodeLabs\Horizon\Page`
  The main class representing a complete HTML page. Implements both Head and Body interfaces and provides rendering capabilities. Supports decorators, fragments, and content management.

- `DecodeLabs\Horizon\Head`
  Interface defining the HTML head section capabilities, including title, meta tags, links (stylesheets, favicons), scripts, and charset management.

- `DecodeLabs\Horizon\Body`
  Interface defining the HTML body section capabilities, including content, body scripts, appendable markup, and layout support.

- `DecodeLabs\Horizon\Decorator`
  Interface for page decorators that can modify Page instances to add common functionality (e.g., analytics, base styles).

- `DecodeLabs\Horizon\Property\LinkCollection`
  Interface for managing link tags (stylesheets, favicons, etc.) with priority support.

- `DecodeLabs\Horizon\Property\MetaCollection`
  Interface for managing meta tags with key-value access.

- `DecodeLabs\Horizon\Property\ScriptCollection`
  Interface for managing script tags in the head section.

- `DecodeLabs\Horizon\Property\BodyScriptCollection`
  Interface for managing script tags in the body section.

- `DecodeLabs\Tagged\Component\Fragment`
  Component for loading and rendering page fragments (PHP files or closures) with dependency injection support via Slingshot.

- `DecodeLabs\Harvest\Transformer\DecodeLabs\Horizon\Page`
  Harvest transformer that converts Page instances to PSR-7 HTML responses.

- `DecodeLabs\Greenleaf\PageAction\Php`
  Greenleaf page action handler that loads PHP fragment files and returns Page instances.

### 3.2 Main Entry Points

The main usage pattern is creating a Page with content:

```php
use DecodeLabs\Horizon\Page;
use DecodeLabs\Tagged as Html;

$page = new Page(function($page) {
    $page->title = 'Hello, World!';
    $page->setMeta('description', 'This is a test page');
    $page->addLink('styles', rel: 'stylesheet', href: '/styles.css');
    yield Html::{'h1'}('Hello, World!');
});
```

For fragment-based pages:

```php
$page = Page::fromFragment('@pages/home.php', param1: 'value1');
```

For decorator usage:

```php
$page->decorate('MyDecorator', '/base/path');
```

---

## 4. Dependencies

### 4.1 Direct Decode Labs Dependencies

From `composer.json`:

- `decodelabs/archetype`
  Used for decorator resolution and class lookup.

- `decodelabs/coercion`
  Type coercion utilities for attribute and property handling.

- `decodelabs/exceptional`
  Enhanced exception handling throughout the package.

- `decodelabs/monarch`
  Single source of truth for service resolution and development mode detection.

- `decodelabs/nuance`
  Debugging and inspection utilities.

- `decodelabs/slingshot`
  Dependency injection invoker for fragment parameter resolution.

- `decodelabs/tagged`
  Markup generation system used for all HTML output.

**Optional integrations:**

- `decodelabs/greenleaf` (optional, dev dependency)
  Detected at runtime if installed, used for page action handling and route generation.

- `decodelabs/harvest` (optional, dev dependency)
  Detected at runtime if installed, used for HTTP response transformation.

### 4.2 External Dependencies

None required for runtime operation.

See `composer.json` for supported PHP versions.

---

## 5. Behaviour & Contracts

### 5.1 Invariants

- Page always renders valid HTML5 document structure with `<!DOCTYPE html>`
- Head and body sections are always present in rendered output
- Title, charset, and other head properties have sensible defaults
- Links, meta tags, and scripts are managed by key for easy access and modification
- Fragment parameters are passed via Slingshot for dependency injection
- Decorators are resolved via Archetype and can accept additional parameters
- Page content can be set as closures, generators, or Fragment instances
- Rendering respects pretty-print setting (typically based on development mode)

### 5.2 Input & Output Contracts

**Page::__construct(mixed content = null):**
- **Input:** Content as closure, generator, Fragment, or null
- **Output:** Page instance with initialized head and body
- **Preconditions:** None
- **Postconditions:** Page is ready for configuration and rendering

**Page::render(bool pretty = false):**
- **Input:** Pretty-print flag (typically from Monarch::isDevelopment())
- **Output:** Buffer containing complete HTML document
- **Preconditions:** None
- **Postconditions:** Output is valid HTML5 with DOCTYPE declaration

**Page::decorate(string|Decorator, mixed ...parameters):**
- **Input:** Decorator class name or instance, plus optional parameters
- **Output:** Returns self for fluent chaining
- **Preconditions:** Decorator must be resolvable via Archetype if string provided
- **Postconditions:** Decorator's decorate() method has been called with Page instance

**Head::setMeta(string key, string|Tag value, array attributes = []):**
- **Input:** Meta tag key, value (string or Tag), and optional attributes
- **Output:** Returns self for fluent chaining
- **Preconditions:** None
- **Postconditions:** Meta tag is stored and will be rendered in head section

**Body::appendBody(string key, Markup value, int priority = 0):**
- **Input:** Key for identification, Markup instance, and optional priority
- **Output:** Returns self for fluent chaining
- **Preconditions:** None
- **Postconditions:** Markup is stored with priority and will be rendered after body content

---

## 6. Error Handling

### 6.1 Exception Types

Horizon throws Exceptional exceptions:

- `Exceptional::NotFound`: When fragment file cannot be found (PageAction\Php)
- `Exceptional::InvalidArgument`: When fragment parameters are invalid (must be key-value pairs)
- `Exceptional::Runtime`: When decorator cannot be resolved or other runtime errors occur

All exceptions use the Exceptional pattern for enhanced stack traces and context.

### 6.2 Error Strategy

Horizon uses a fail-fast error strategy. Invalid inputs, missing files, or unresolvable decorators result in exceptions being thrown immediately. The package does not attempt to recover from errors or provide fallback behavior.

---

## 7. Configuration & Extensibility

### 7.1 Configuration

No runtime configuration is required. Horizon works out of the box with sensible defaults. Configuration is done through Page properties:

- Language (defaults to 'en')
- Charset (defaults to UTF-8)
- Title and meta tags
- Links and scripts
- Body classes and attributes

Pretty-printing is typically controlled by Monarch's development mode detection.

### 7.2 Extension Points

Horizon supports extension via:

- **Custom Decorator implementations:** Implement `DecodeLabs\Horizon\Decorator` interface to create reusable page modifiers
- **Fragment-based pages:** Use Fragment component to load PHP files or closures as page content
- **Custom property collections:** Extend property collection interfaces for additional head/body management
- **Harvest transformers:** Implement custom transformers for different response formats
- **Greenleaf page actions:** Create custom PageAction implementations for different fragment loading strategies

---

## 8. Interactions with Other Packages

Horizon is designed to integrate with:

- **`decodelabs/tagged`**
  Uses Tagged for all markup generation. Page content and all HTML elements are built using Tagged interfaces.

- **`decodelabs/harvest`**
  Provides transformer that converts Page instances to PSR-7 HTML responses. Used automatically by Harvest middleware when Page is returned.

- **`decodelabs/greenleaf`**
  Provides PageAction\Php that loads PHP fragment files and returns Page instances. Enables file-based page routing.

- **`decodelabs/slingshot`**
  Used for dependency injection in Fragment rendering, allowing fragments to receive services and parameters.

- **`decodelabs/monarch`**
  Used for development mode detection (affects pretty-printing) and path resolution for fragment loading.

Design assumptions:

- Tagged is available for all markup generation
- Harvest transformer system is available if HTTP integration is needed
- Greenleaf is available if file-based routing is desired
- Slingshot is available for fragment dependency injection

---

## 9. Usage Examples

### 9.1 Basic Page Creation

```php
use DecodeLabs\Horizon\Page;
use DecodeLabs\Tagged as Html;

$page = new Page(function($page) {
    $page->title = 'Hello, World!';
    $page->setMeta('description', 'This is a test page');
    
    $page->addLink('styles', rel: 'stylesheet', href: '/styles.css');
    $page->addBodyScript('bundle', src: '/bundle.js');
    
    $page->bodyTag->addClass('section-home');
    
    yield Html::{'h1'}('Hello, World!');
    yield Html::{'p'}('This is a test page');
});
```

### 9.2 Using Decorators

```php
namespace DecodeLabs\Horizon\Decorator;

use DecodeLabs\Horizon\Decorator;
use DecodeLabs\Horizon\Page;

class AnalyticsDecorator implements Decorator
{
    public function decorate(
        Page $page,
        string $trackingId
    ): void {
        $page->addBodyScript(
            'analytics',
            src: 'https://analytics.example.com/script.js',
            'data-tracking-id' => $trackingId
        );
    }
}

$page = new Page(function($page) {
    yield Html::{'h1'}('My Page');
});

$page->decorate('AnalyticsDecorator', 'UA-123456-1');
```

### 9.3 Fragment-Based Pages

```php
// Load from file path
$page = Page::fromFragment('@pages/home.php', userId: 123);

// Or use Fragment directly
use DecodeLabs\Tagged\Component\Fragment;

$fragment = new Fragment('@pages/about.php', companyName: 'Acme Corp');
$page = new Page($fragment);
```

### 9.4 Greenleaf Integration

```php
use DecodeLabs\Greenleaf\Action;
use DecodeLabs\Horizon\Page;

class MyAction implements Action
{
    public function get(): Page
    {
        return new Page(function($page) {
            $page->title = 'My Page';
            yield 'Content here';
        });
    }
}
```

---

## 10. Implementation Notes (For Contributors)

### 10.1 Internal Architecture

At a high level, Horizon:

- Uses trait composition to combine Head and Body functionality in Page class
- Manages collections (links, meta, scripts) with priority support for ordering
- Integrates Fragment component for file-based or closure-based content loading
- Uses Tagged's Tag and Buffer classes for all HTML generation
- Provides Harvest transformer for seamless HTTP response conversion
- Supports decorator pattern via Archetype resolution and Slingshot invocation

Contributors should:

- Maintain separation between Head and Body concerns via interfaces and traits
- Preserve Tagged integration for all markup generation
- Keep decorator system flexible for extension
- Ensure Fragment loading works with both file paths and closures
- Maintain compatibility with Harvest and Greenleaf integration points

### 10.2 Performance Considerations

- Page rendering performs string concatenation for HTML output
- Collections (links, meta, scripts) use arrays with key-based access for O(1) lookups
- Fragment loading caches loaded fragments when possible
- Pretty-printing adds overhead and should be disabled in production
- Decorator resolution uses Archetype which may involve class loading

### 10.3 Gotchas & Historical Decisions

- **Fragment parameters:** Must be passed as key-value pairs, not positional arguments, to support Slingshot dependency injection
- **Decorator resolution:** Decorators are resolved via Archetype using ucfirst() transformation of the name, so 'MyDecorator' resolves to class in DecodeLabs\Horizon\Decorator namespace or registered Archetype namespaces
- **Priority system:** Links, scripts, and appendable markup use priority for ordering, with lower numbers appearing first
- **Fragment binding:** Fragments can be bound to objects for closure context, but only if loaded from files
- **Pretty printing:** Controlled by Monarch::isDevelopment() in transformer, but can be overridden in render() calls

---

## 11. Testing & Quality

### 11.1 Testing Strategy

Tests should cover:

- Page creation with various content types (closures, generators, fragments)
- Head section management (title, meta, links, scripts, charset)
- Body section management (content, body scripts, appendable markup)
- Decorator application and resolution
- Fragment loading from files and closures
- Fragment parameter passing and Slingshot integration
- Page rendering with pretty-printing on/off
- Harvest transformer conversion to PSR-7 responses
- Greenleaf PageAction integration
- Collection management (add, remove, clear operations)
- Priority-based ordering of links, scripts, and markup

### 11.2 Quality Signals

From the Decode Labs package index (at time of writing):

- **Code:** 4.5
- **Readme:** 3
- **Docs:** 0
- **Tests:** 0

Horizon is a mature, production-ready package with high code quality. The README provides good usage examples, though comprehensive documentation (this spec) was not present at indexing time. Test coverage is planned but not yet implemented. The package demonstrates solid integration with Tagged, Harvest, and Greenleaf.

---

## 12. Roadmap & Future Ideas

Non-binding ideas:

- Comprehensive test suite covering all page construction and rendering scenarios
- Additional built-in decorators for common use cases
- Enhanced fragment loading with caching strategies
- Support for page layouts and nested page composition
- Performance optimizations for large page rendering
- Enhanced meta tag management with Open Graph and Twitter Card support
- Built-in support for critical CSS injection
- Page preloading and resource hints management

---

## 13. References

- **Chorus docs:**
  - Architecture principles
  - Package taxonomy & clusters
  - Backwards compatibility strategy (once published)

- **Related packages:**
  - `decodelabs/tagged` (markup generation foundation)
  - `decodelabs/harvest` (HTTP response transformation)
  - `decodelabs/greenleaf` (routing and page actions)
  - `decodelabs/slingshot` (fragment dependency injection)
  - `decodelabs/monarch` (development mode detection)
  - `decodelabs/scrutiny` (uses Horizon for HTML document components)

- **Repository:**
  - `https://github.com/decodelabs/horizon`

---

> This spec is intended to stay in sync with the **actual behaviour** of the package.
> When you make significant changes to the public surface or semantics, please update this document and, where applicable, add or update ADRs in Chorus.

