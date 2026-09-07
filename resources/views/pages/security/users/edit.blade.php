<x-layouts.premium
    title="Benutzer bearbeiten"
    subtitle="Rolle, Aktivstatus und Passwort des Benutzers verwalten."
>
    <section class="premium-card">
        <form method="POST" action="{{ route('security.users.update', $user) }}">
            @method('PUT')

            @include('pages.security.users._form')
        </form>
    </section>
</x-layouts.premium>
