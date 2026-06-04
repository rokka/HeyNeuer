<x-mail::message>
# Willkommen bei Hey, Alter! Essen

Hallo,

Sie wurden eingeladen, einen Zugang für die Computerverwaltung von Hey, Alter! Essen anzulegen.

Bitte klicken Sie auf den folgenden Link, um Ihre Registrierung abzuschließen — Sie vergeben dort Ihr Passwort und ergänzen Ihren Namen.

<x-mail::button :url="$acceptUrl">
Registrierung abschließen
</x-mail::button>

Der Link ist **{{ $expiresIn }} Stunden** gültig.

Falls Sie diese Einladung nicht erwartet haben, können Sie diese E-Mail ignorieren.

Viele Grüße<br>
{{ config('app.name') }}
</x-mail::message>
