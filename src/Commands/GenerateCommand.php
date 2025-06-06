<?php

namespace Apih\JsLang\Commands;

use Apih\JsLang\JsLang;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class GenerateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jslang:generate
                {--hash-algo=crc32 : Specify hash algorithm for file versioning}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate JavaScript files containing all language localization messages';

    protected Filesystem $filesystem;
    protected JsLang $jsLang;
    protected string $hashAlgo;
    protected int|null $hashLength;
    protected array $hashes = [];

    /**
     * Create a new command instance.
     */
    public function __construct(Filesystem $filesystem, JsLang $jsLang)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->jsLang = $jsLang;
        $this->hashAlgo = 'crc32';
        $this->hashLength = null;

        $this->filesystem->ensureDirectoryExists($this->jsLang->publicLangPath());
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Get hash options for versioning
        [$this->hashAlgo, $this->hashLength] = array_pad(explode(',', $this->option('hash-algo')), 2, null);

        // Clear generated files
        $this->callSilent('jslang:clear');

        // Get and store contents
        foreach (config('jslang.locales') as $locale) {
            foreach (['short', 'long', 'all'] as $type) {
                $this->storeFile($locale, $type, $this->jsLang->getContents($locale, $type, true));
            }
        }

        // Store hashes
        $this->filesystem->put($this->jsLang->publicLangPath('hashes.json'), json_encode($this->hashes, JSON_PRETTY_PRINT));

        $this->info('JS files for front-end language localization has been created successfully!');
    }

    /**
     * Store the language contents in a JS file.
     */
    protected function storeFile(string $locale, string $type, string $contents): void
    {
        $hash = config('jslang.hash_prefix') . substr(hash($this->hashAlgo, $contents), 0, $this->hashLength);
        $this->hashes["{$locale}.{$type}"] = $hash;

        $this->filesystem->ensureDirectoryExists($this->jsLang->publicLangPath($locale));
        $this->filesystem->put($this->jsLang->publicLangPath("{$locale}/{$type}.{$hash}.js"), $contents);
    }
}
