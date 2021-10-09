<?php

namespace Apih\JsLang\Commands;

use Apih\JsLang\JsLang;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class ClearCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jslang:clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete all generated JavaScript files';

    /**
     * Properties.
     */
    protected Filesystem $filesystem;
    protected JsLang $jsLang;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @param  \Apih\JsLang\JsLang  $jsLang
     * @return void
     */
    public function __construct(Filesystem $filesystem, JsLang $jsLang)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
        $this->jsLang = $jsLang;
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        // Delete all caches
        foreach (config('jslang.locales') as $locale) {
            foreach (['short', 'long', 'all'] as $type) {
                cache()->forget("jslang-{$locale}-{$type}");
            }
        }

        // Delete all generated files
        $files = $this->filesystem->allFiles($this->jsLang->publicLangPath());

        foreach ($files as $file) {
            $this->filesystem->delete($file);
        }

        $this->info('JS files for front-end language localization has been deleted successfully!');
    }
}
