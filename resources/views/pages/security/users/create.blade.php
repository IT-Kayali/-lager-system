<x-layouts.premium title="Benutzer erstellen" subtitle="Neuen Systembenutzer mit Rolle und Passwort anlegen.">
    <section class="premium-card">
        <form method="POST" action="{{ route('security.users.store') }}">
            @include('pages.security.users._form')
        </form>
    </section>
</x-layouts.premium>
