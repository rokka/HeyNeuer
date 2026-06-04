<x-mail::message>
# Neue Selbst-Registrierung

Eine Person hat sich gerade selbst über den Registrierungslink ein Konto in der Hey, Alter! Essen Computerverwaltung angelegt:

- **Name:** {{ $newUser->name }}
- **E-Mail:** {{ $newUser->email }}
- **Zeitpunkt:** {{ $newUser->registered_at?->format('d.m.Y H:i') }} Uhr

Der Benutzer hat **keine Administrator-Rechte**.

Falls die Registrierung nicht erwünscht war, können Sie den Benutzer in der Benutzerverwaltung löschen und gegebenenfalls den Registrierungs-Token rotieren.

<x-mail::button :url="$usersIndex">
Zur Benutzerverwaltung
</x-mail::button>

Viele Grüße<br>
Hey, Alter! Essen
</x-mail::message>
