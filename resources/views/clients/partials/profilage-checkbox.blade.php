<input type="checkbox" class="form-check-input js-profilage"
       data-url="{{ route('clients.profilage', $client) }}"
       title="{{ $client->profilage_fait_at ? 'Fait le ' . $client->profilage_fait_at->format('d/m/Y') : 'Pas encore fait' }}"
       dusk="chk-profilage-{{ $client->id }}"
       @checked($client->profilage_fait_at)>
