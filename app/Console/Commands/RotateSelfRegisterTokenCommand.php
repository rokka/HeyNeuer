<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RotateSelfRegisterTokenCommand extends Command
{
    protected $signature = 'auth:rotate-self-register-token {--show : Nur den aktuellen Token anzeigen, nichts ändern}';

    protected $description = 'Generiert einen neuen kryptischen Self-Registration-Token und schreibt ihn in die .env-Datei.';

    public function handle(): int
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->error('.env nicht gefunden.');
            return self::FAILURE;
        }

        if ($this->option('show')) {
            $current = config('auth.self_registration_token');
            if (empty($current)) {
                $this->warn('Self-Registration ist aktuell deaktiviert (kein Token in .env).');
                return self::SUCCESS;
            }
            $this->info('Aktueller Self-Registration-Link:');
            $this->line(url('/register/' . $current));
            return self::SUCCESS;
        }

        $newToken = bin2hex(random_bytes(32));
        $envContent = file_get_contents($envPath);

        if (preg_match('/^SELF_REGISTRATION_TOKEN=.*/m', $envContent)) {
            $envContent = preg_replace(
                '/^SELF_REGISTRATION_TOKEN=.*/m',
                'SELF_REGISTRATION_TOKEN=' . $newToken,
                $envContent
            );
        } else {
            $envContent = rtrim($envContent, "\n") . "\n\nSELF_REGISTRATION_TOKEN=" . $newToken . "\n";
        }

        file_put_contents($envPath, $envContent);

        $this->call('config:clear');

        $this->info('Neuer Self-Registration-Token wurde generiert und in .env gespeichert.');
        $this->info('Neuer Link:');
        $this->line(url('/register/' . $newToken));
        $this->newLine();
        $this->warn('Der alte Link funktioniert ab sofort NICHT mehr.');

        return self::SUCCESS;
    }
}
