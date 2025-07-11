<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class GeneratePassword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:password {password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a hashed password using Laravel Hash::make';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $password = $this->argument('password');
        $hashedPassword = Hash::make($password);
        $this->info("Hashed Password: $hashedPassword");
    }
}
