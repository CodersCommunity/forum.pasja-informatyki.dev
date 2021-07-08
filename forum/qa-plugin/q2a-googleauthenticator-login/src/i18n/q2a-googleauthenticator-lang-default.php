<?php

return [
    'title'              => 'Weryfikacja dwuetapowa',
    'plugin_is_enabled'  => 'Weryfikacja dwuetapowa jest włączona od ',
    'plugin_is_disabled' => 'Weryfikacja dwuetapowa jest wyłączona',
    'enable_plugin'      => 'Włącz',
    'disable_plugin'     => 'Wyłącz',
    'enabled_plugin'      => 'Jeśli chcesz wyłączyć użytkownikom 2FA, odznacz ten checkbox',
    'disabled_plugin'    => 'Włącz użytkownikom 2FA',

    'default_auth' => '(Domyślna)',
    'confirm'      => 'Gotowe',
    'next'         => 'Dalej',
    'cancel'       => 'Anuluj',
    'generate'     => 'Wygeneruj',

    '2fa_setup_error'       => 'Nie udało się włączyć autoryzacji dwuetapowej. Zgłoś ten problem administratorowi.',
    'saved_plugin_settings' => 'Konfiguracja została zapisana',
    'save_settings'         => 'Zapisz',
    'secret_error'          => 'Nieprawidłowy sekret. Spróbuj ponownie lub zgłoś adminowi.',

    'recover_code_page_info' => 'Zalogowałeś się z wykorzystaniem kodu awaryjnego. Nie jest on już aktywny. Jeśli nie możesz już logować się korzystając z weryfikacji dwuetapowej pamiętaj, aby w ustawieniach wyłączyć logowanie dwuetapowe. Inaczej nie będziesz mógł/mogła się już zalogować.',
    '2fa_data_info'          => 'Właśnie włączyłeś weryfikację dwuetapową. To dobry krok na drodze do całkowitego zabezpieczenia Twojego konta. Aby skorzystać z weryfikacji dwuetapowej, zeskanuj ten kod QR swoim telefonem {{ QR_CODE }} {{ ERROR_START }}Nie możesz zeskanować kodu QR? Możesz też wpisać ten kod do swojej aplikacji (tzw. secret): {{ SECRET }}{{ ERROR_END }}W razie sytuacji awaryjnej, w miejscu kodu z aplikacji możesz również wpisać ten kod zapasowy: {{ RECOVERY_CODE }} Pamiętaj że jest on wykorzystywany w wyjątkowych sytuacjach i działa tylko na jedno logowanie - później bedziesz musial wygenerować nowy kod.',
    'code_input'             => 'Kod weryfikacyjny:',
    'send'                   => 'Wyślij',
    'invalid_code'           => 'Błędny kod'

];
