<?php

declare(strict_types=1);

namespace Melasistema\HydeMultilanguageModule\Commands;

use Hyde\Console\Commands\PublishHomepageCommand;
use Hyde\Framework\Services\ViewDiffService;
use Hyde\Pages\BladePage;
use Hyde\Console\Concerns\Command;
use Hyde\Console\Concerns\AsksToRebuildSite;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Collection;

use function Hyde\unixsum_file;
use function array_key_exists;
use function file_exists;
use function str_replace;
use function strstr;

class PublishMultilingualHomepageCommand extends PublishHomepageCommand
{
    use AsksToRebuildSite;

    /** @var string */
    protected $signature = 'publish:multilingual-homepage {homepage? : The name of the page to publish}
                                {--force : Overwrite any existing files}';

    /** @var string */
    protected $description = 'Publish the default homepage (index) and create symlinks for supported languages';

    /** @var array<string, array{name: string, description: string, group: string}> */
    protected array $options = [
        'welcome' => [
            'name' => 'Welcome',
            'description' => 'The default welcome page.',
            'group' => 'hyde-welcome-page',
        ],
        'posts' => [
            'name' => 'Posts Feed',
            'description' => 'A feed of your latest posts. Perfect for a blog site!',
            'group' => 'hyde-posts-page',
        ],
        'blank' => [
            'name' => 'Blank Starter',
            'description' => 'A blank Blade template with just the base layout.',
            'group' => 'hyde-blank-page',
        ],
    ];

    public function handle(): int
    {
        // Select the homepage template to publish
        $selected = $this->parseSelection();

        if (! $this->canExistingFileBeOverwritten()) {
            $this->error('A modified index.blade.php file already exists. Use --force to overwrite.');
            return 409;
        }

        // Publish the homepage template
        $tagExists = array_key_exists($selected, $this->options);

        Artisan::call('vendor:publish', [
            '--tag' => $this->options[$selected]['group'] ?? $selected,
            '--force' => true,
        ], $tagExists ? null : $this->output);

        if ($tagExists) {
            $this->infoComment("Published page [$selected]");

            // Now create symlinks for the supported languages
            $this->createLanguageSymlinks('index.blade.php');

            $this->askToRebuildSite();
        }

        return $tagExists ? Command::SUCCESS : 404;
    }

    protected function createLanguageSymlinks(string $file): void
    {
        // Define the supported languages from config
        $languages = config('hyde-multilanguage.supported_languages', ['en', 'it', 'de']);
        $pagesDirectory = base_path('_pages');

        foreach ($languages as $language) {
            $languageDirectory = "{$pagesDirectory}/{$language}";

            // Ensure the language directory exists
            if (! file_exists($languageDirectory)) {
                mkdir($languageDirectory, 0755, true);
            }

            $languageFilePath = "{$languageDirectory}/{$file}";

            // Create symlink for the language if it doesn't exist
            if (! file_exists($languageFilePath)) {
                if (symlink("{$pagesDirectory}/{$file}", $languageFilePath)) {
                    $this->info("Symlink created for {$file} in {$language} directory");
                } else {
                    $this->error("Failed to create symlink for {$file} in {$language} directory");
                }
            } else {
                $this->warn("Symlink already exists for {$file} in {$language} directory");
            }
        }
    }

    protected function parseSelection(): string
    {
        return $this->argument('homepage') ?? $this->parseChoiceIntoKey($this->promptForHomepage());
    }

    protected function promptForHomepage(): string
    {
        return $this->choice(
            'Which homepage do you want to publish?',
            $this->formatPublishableChoices(),
            0
        );
    }

    protected function formatPublishableChoices(): array
    {
        return $this->getTemplateOptions()->map(function (array $option, string $key): string {
            return  "<comment>$key</comment>: {$option['description']}";
        })->values()->toArray();
    }

    /** @return Collection<array{name: string, description: string, group: string}> */
    protected function getTemplateOptions(): Collection
    {
        return new Collection($this->options);
    }

    protected function parseChoiceIntoKey(string $choice): string
    {
        return strstr(str_replace(['<comment>', '</comment>'], '', $choice), ':', true);
    }

    protected function canExistingFileBeOverwritten(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        if (! file_exists(BladePage::path('index.blade.php'))) {
            return true;
        }

        return $this->isTheExistingFileADefaultOne();
    }

    protected function isTheExistingFileADefaultOne(): bool
    {
        return ViewDiffService::checksumMatchesAny(unixsum_file(BladePage::path('index.blade.php')));
    }
}