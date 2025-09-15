<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PasswordResetToken;
use Carbon\Carbon;

class ClearExpiredTokens extends Command
{
    protected $signature = 'tokens:clear';
    protected $description = 'Clear expired password reset tokens';

    public function handle()
    {
        PasswordResetToken::where('created_at', '<', Carbon::now()->subMinutes(30))->delete();
        $this->info('Expired tokens cleared successfully.');
    }
}