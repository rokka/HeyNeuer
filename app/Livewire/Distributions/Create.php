<?php

namespace App\Livewire\Distributions;

use App\Enums\ComputerStatus;
use App\Models\Computer;
use App\Models\Distribution;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

class Create extends Component
{
    #[Url(as: 'first_name')]
    public string $first_name = '';

    #[Url(as: 'last_name')]
    public string $last_name = '';

    #[Url(as: 'birthdate')]
    public string $birthdate = '';

    public string $computer_number_input = '';

    public function rules(): array
    {
        return [
            'first_name'            => ['required', 'string', 'max:100'],
            'last_name'             => ['required', 'string', 'max:100'],
            'birthdate'             => ['required', 'date', 'before:today'],
            'computer_number_input' => ['required', 'string', 'max:30'],
        ];
    }

    protected function messages(): array
    {
        return [
            'birthdate.before' => 'Das Geburtsdatum muss in der Vergangenheit liegen.',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        // 1) Computernummer normalisieren
        $number = Distribution::normalizeComputerNumber($validated['computer_number_input']);
        if ($number === null) {
            $this->addError('computer_number_input', 'Ungültige Computernummer. Erlaubt: "HA-E-1234" oder "1234".');
            return;
        }

        $computer = Computer::where('number', $number)->first();
        if (! $computer) {
            $this->addError('computer_number_input', "Computer mit der Nummer {$number} wurde nicht gefunden.");
            return;
        }

        // 2) Hash bilden
        $hash = Distribution::buildRecipientHash(
            $validated['first_name'],
            $validated['last_name'],
            $validated['birthdate']
        );

        // 3) Duplikat-Check (gleiche Person darf nur einmal)
        if (Distribution::where('recipient_hash', $hash)->exists()) {
            $this->addError('recipient', 'Diese Person hat bereits einen Computer erhalten.');
            return;
        }

        // 4) Anlegen — Klartextdaten werden NIE gespeichert
        Distribution::create([
            'computer_id'    => $computer->id,
            'user_id'        => Auth::id(),
            'distributed_at' => now(),
            'recipient_hash' => $hash,
        ]);

        $computer->update(['status' => ComputerStatus::Delivered]);

        session()->flash('status', "Ausgabe von Computer {$number} wurde erfasst.");

        // Felder leeren (besonders die personenbezogenen Daten — sind zwar
        // nicht persistiert, sollen aber nicht im Browser bleiben)
        $this->reset(['first_name', 'last_name', 'birthdate', 'computer_number_input']);

        $this->redirectRoute('distributions.index', navigate: true);
    }

    #[Layout('layouts.app')]
    #[Title('Neue Ausgabe')]
    public function render(): mixed
    {
        return view('livewire.distributions.create');
    }
}
