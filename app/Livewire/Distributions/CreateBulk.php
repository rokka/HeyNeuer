<?php

namespace App\Livewire\Distributions;

use App\Enums\ComputerStatus;
use App\Models\Computer;
use App\Models\Distribution;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

class CreateBulk extends Component
{
    public string $computer_number_input = '';

    public string $comment = '';

    /** @var array<int, string> Bereits erfasste, normalisierte Computernummern (HA-E-####). */
    public array $numbers = [];

    public function addNumber(): void
    {
        $raw = trim($this->computer_number_input);
        if ($raw === '') {
            $this->addError('computer_number_input', 'Bitte eine Computernummer eingeben.');
            return;
        }

        $number = Distribution::normalizeComputerNumber($raw);
        if ($number === null) {
            $this->addError('computer_number_input', 'Ungültige Computernummer. Erlaubt: "HA-E-1234" oder "1234".');
            return;
        }

        if (in_array($number, $this->numbers, true)) {
            $this->addError('computer_number_input', "Computer {$number} wurde bereits erfasst.");
            return;
        }

        $computer = Computer::where('number', $number)->first();
        if (! $computer) {
            $this->addError('computer_number_input', "Computer mit der Nummer {$number} wurde nicht gefunden.");
            return;
        }

        if ($computer->status === ComputerStatus::Delivered) {
            $this->addError('computer_number_input', "Computer {$number} wurde bereits ausgegeben.");
            return;
        }

        $this->numbers[] = $number;
        $this->computer_number_input = '';
    }

    public function removeNumber(int $index): void
    {
        if (array_key_exists($index, $this->numbers)) {
            unset($this->numbers[$index]);
            $this->numbers = array_values($this->numbers);
        }
    }

    public function save(): void
    {
        if (empty($this->numbers)) {
            $this->addError('numbers', 'Bitte mindestens einen Computer erfassen.');
            return;
        }

        $computers = Computer::whereIn('number', $this->numbers)->get();
        $comment   = trim($this->comment) !== '' ? $this->comment : null;
        $userId    = Auth::id();
        $now       = now();

        DB::transaction(function () use ($computers, $comment, $userId, $now) {
            foreach ($computers as $computer) {
                Distribution::create([
                    'computer_id'    => $computer->id,
                    'user_id'        => $userId,
                    'distributed_at' => $now,
                    'recipient_hash' => null,
                    'comment'        => $comment,
                ]);
            }

            Computer::whereIn('id', $computers->pluck('id'))
                ->update(['status' => ComputerStatus::Delivered->value]);
        });

        $count = $computers->count();
        session()->flash('status', "{$count} Computer wurden ausgegeben.");

        $this->reset(['numbers', 'computer_number_input', 'comment']);

        $this->redirectRoute('distributions.index', navigate: true);
    }

    #[Layout('layouts.app')]
    #[Title('Massenausgabe')]
    public function render(): mixed
    {
        return view('livewire.distributions.create-bulk');
    }
}
