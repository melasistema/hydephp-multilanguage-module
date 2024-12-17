<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Pages;

use Hyde\Foundation\Kernel\PageCollection;
use Hyde\Framework\Features\Blogging\Models\FeaturedImage;
use Hyde\Framework\Features\Blogging\Models\PostAuthor;
use Hyde\Markdown\Contracts\FrontMatter\BlogPostSchema;
use Hyde\Pages\MarkdownPost;
use Hyde\Support\Models\DateString;

use function array_merge;

/**
 * Multilingual Markdown Post.
 *
 * Extends the HydePHP MarkdownPost class to add support for multilingual features.
 */
class MultilingualMarkdownPost extends MarkdownPost implements BlogPostSchema
{
    public string $locale;

    /**
     * Constructor to set locale.
     *
     * @param string $identifier
     * @param string $locale
     */
    public function __construct(string $identifier, string $locale = 'en')
    {
        $this->locale = $locale;
        parent::__construct($identifier);
    }

    /**
     * Override to include locale-specific fields in the array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'locale' => $this->locale,
            'localizedTitle' => $this->getLocalizedTitle(),
            'localizedDescription' => $this->getLocalizedDescription(),
        ]);
    }

    /**
     * Get the localized title.
     *
     * @return string
     */
    public function getLocalizedTitle(): string
    {
        return __("posts.{$this->locale}.{$this->identifier}.title");
    }

    /**
     * Get the localized description.
     *
     * @return string
     */
    public function getLocalizedDescription(): string
    {
        return __("posts.{$this->locale}.{$this->identifier}.description");
    }

    /**
     * Get the latest posts for a specific locale.
     *
     * @param string $locale
     * @return \Hyde\Foundation\Kernel\PageCollection
     */
    public static function getLatestPostsForLocale(string $locale): PageCollection
    {
        return static::all()->filter(function (self $post) use ($locale): bool {
            return $post->locale === $locale;
        })->sortByDesc(function (self $post): int {
            return $post->date?->dateTimeObject->getTimestamp() ?? 0;
        });
    }
}
