<?php

/**
 * Pesan validasi Bahasa Indonesia (disingkat; tambahkan aturan lain bila dibutuhkan).
 */
return [
    'required'  => ':attribute wajib diisi.',
    'email'     => ':attribute harus berupa alamat email yang valid.',
    'string'    => ':attribute harus berupa teks.',
    'unique'    => ':attribute sudah terdaftar.',
    'min'       => [
        'string' => ':attribute minimal :min karakter.',
    ],
    'max'       => [
        'string' => ':attribute maksimal :max karakter.',
    ],
    'attributes' => [],
];
