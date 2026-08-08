<?php

it('shows customer email like supplier contact information', function () {
    $view = file_get_contents(resource_path('views/pages/customers/index.blade.php'));

    expect($view)
        ->toContain('class="customer-contact-stack"')
        ->toContain('href="mailto:{{ $customer->email }}"')
        ->toContain('bi bi-envelope')
        ->toContain("{{ \$customer->email }}")
        ->toContain('Keine E-Mail')
        ->toContain('<x-whatsapp-link');
});
