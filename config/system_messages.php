<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Response messages to Admin from API
    |--------------------------------------------------------------------------
    |
    | Custom messages for Admin from response API
    |
    */
    'registration' => [
        'name' => 'РЕГИСТРАЦИЯ',
        'message' => 'Уррра, у нас новенький!',
    ],
    'could_not_register' => [
        'name' => 'ПРОБЛЕМЫ НА СЕРВИСЕ',
        'message' => 'Пользователь не смог зарегистрироваться!',
    ],
    'could_not_login' => [
        'message' => 'Пользователь не смог войти - ',
    ],
    'could_not_change_password' => [
        'message' => 'Пользователь не смог сменить пароль - ',
    ],
    'conference_registration_success' => [
        'name' => 'УСПЕШНАЯ ПОДАЧА ЗАЯВКИ НА СТАЖИРОВКУ',
        'message' => 'Уррра, у нас новая регистрация на стажировку (но пока только заявка)!',
    ],
    'conference_registration_failed' => [
        'name' => 'ОШИБКА ПРИ ПОДАЧЕ ЗАЯВКИ НА СТАЖИРОВКУ',
        'message' => 'Пользователь не смог зарегистрироваться на стажировку - ',
    ],
    'invoice_generation_success' => [
        'name' => 'УСПЕШНАЯ ГЕНЕРАЦИЯ ИНВОЙСА',
        'message' => 'Уррра, пользователь сгенерировал инвойс - ',
    ],
    'invoice_generation_failed' => [
        'name' => 'ОШИБКА ПРИ ГЕНЕРАЦИИ ИНВОЙСА',
        'message' => 'Пользователь не смог сгенерировать инфойс - ',
    ],
    'cm_publication_success' => [
        'name' => 'УСПЕШНАЯ ПУБЛИКАЦИЯ МАТЕРИАЛОВ КОЛЛЕКТИВНОЙ МОНОГРАФИИ',
        'message' => 'Уррра, у нас новые материалы к коллективной монографии на публикацию! - ',
    ],
    'cm_publication_failed' => [
        'name' => 'ОШИБКА ПРИ ПУБЛИКАЦИИ МАТЕРИАЛОВ КОЛЛЕКТИВНОЙ МОНОГРАФИИ',
        'message' => 'Пользователь не смог подать коллективную монографию(( - ',
    ],
    'sm_publication_success' => [
        'name' => 'УСПЕШНАЯ ПУБЛИКАЦИЯ МОНОГРАФИИ',
        'message' => 'Уррра, у нас новая монография на публикацию! - ',
    ],
    'sm_publication_failed' => [
        'name' => 'ОШИБКА ПРИ ПУБЛИКАЦИИ МОНОГРАФИИ',
        'message' => 'Пользователь не смог подать монографию(( - ',
    ],
    'constructor_publication_success' => [
        'name' => 'УСПЕШНАЯ ПУБЛИКАЦИЯ МАТЕРИАЛОВ ЧЕРЕЗ КОНСТРУКТОР',
        'message' => 'Уррра, у нас новые материалы поданы через конструктор',
    ],
    'constructor_publication_failed' => [
        'name' => 'ОШИБКА ПРИ ПУБЛИКАЦИИ МАТЕРИАЛОВ ЧЕРЕЗ КОНСТРУКТОР',
        'message' => 'Пользователь не смог подать материалы через конструктор(( - ',
    ],
    'email_not_sent' => [
        'name' => 'ОШИБКА ПРИ ОТПРАВКЕ E-MAIL',
        'message' => 'Пользователю не было отправлено сообщение - ',
    ],
    'critical_server_error' => [
        'name' => '500-Я ОШИБКА НА СЕРВЕРЕ',
        'message' => 'У пользователя возникла критическая ошибка на стороне сервера',
    ],
];
