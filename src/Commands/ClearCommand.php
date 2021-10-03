<?php

namespace Apih\JsLang\Commands;

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

    /** @var \Illuminate\Filesystem\Filesystem */
    protected Filesystem $filesystem;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $filesystem
     * @return void
     */
    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();

        $this->filesystem = $filesystem;
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
        $files = $this->filesystem->allFiles(public_path(config('jslang.public_lang_dir')));

        foreach ($files as $file) {
            $this->filesystem->delete($file);
        }

        $this->info('JS files for front-end language localization has been deleted successfully!');
    }
}
