<?php declare(strict_types=1);

namespace App\Service\Url;

class UrlParse implements \Stringable
{
    protected self $url;

    protected string $scheme;

    protected string $host = '';

    protected ?int $port = null;

    protected string $path = '';

    protected QueryParameterBag $query;

    public function __construct()
    {
        $this->scheme = '';
        $this->query = new QueryParameterBag();
    }

    public static function create(): static
    {
        return new static();
    }

    public static function fromString(string $url): static
    {
        $toUrl = new static();

        return static::make($url, $toUrl);
    }

    protected static function make(string $fromUrl, self $toUrl): static
    {
        if (!$parts = parse_url($fromUrl)) {
            throw new \InvalidArgumentException('Invalid URL');
        }

        $toUrl->scheme = $parts['scheme'] ?? '';
        $toUrl->host = $parts['host'] ?? '';
        $toUrl->port = $parts['port'] ?? null;
        $toUrl->path = $parts['path'] ?? '/';
        $toUrl->query = QueryParameterBag::fromString($parts['query'] ?? '');

        return $toUrl;
    }

    public function createUrl(): static
    {
        return $this->buildUrl()->withScheme()->withHost()->withPort()->withPath();
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getQuery(): string
    {
        return (string)$this->query;
    }

    public function getAllQueryParameters(): array
    {
        return $this->query->all();
    }

    public function withQueryParameters(array $parameters): static
    {
        $parameters = array_merge($this->getAllQueryParameters(), $parameters);
        $url = clone $this;
        $url->query = new QueryParameterBag($parameters);

        $url->url->query = $url->query;

        return $url;
    }

    public function withUTM(): static
    {
        if ((bool)config('utm.include') === false) {
            return clone $this;
        }

        $utmParams = config('utm.params');

        $url = clone $this;

        $utmParams['utm_medium'] = $url->buildUrl()->withPath()->withQueryParameters([])->getUrl()->__toString();

        return clone $this->withQueryParameters($utmParams);
    }

    public function withScheme(): static
    {
        $url = clone $this;

        $url->url->scheme = $url->getScheme();

        return $url;
    }

    public function withHost(): static
    {
        $url = clone $this;

        $url->url->host = $url->getHost();

        return $url;
    }

    public function withPort(): static
    {
        $url = clone $this;

        $url->url->port = $url->getPort();

        return $url;
    }

    public function withPath(): static
    {
        $url = clone $this;

        $url->url->path = $url->getPath();

        return $url;
    }

    public function buildUrl(): static
    {
        $url = clone $this;

        $url->url = self::create();

        return $url;
    }

    public function getUrl(): self
    {
        return $this->url;
    }

    public function getUrlToString(): string
    {
        return $this->getUrl()->__toString();
    }

    public function __toString(): string
    {
        $url = '';

        if ($this->getScheme() !== '') {
            $url .= $this->getScheme().'://';
        }

        if ($this->getHost() !== '') {
            $url .= $this->getHost();
        }

        if ($this->getPort() !== null) {
            $url .= ':' . $this->getPort();
        }

        if ($this->getPath() !== '/') {
            $url .= $this->getPath();
        }

        if ($this->getQuery() !== '') {
            $url .= '?'.$this->getQuery();
        }

        return $url;
    }

    public function __clone()
    {
        $this->query = clone $this->query;
    }
}
