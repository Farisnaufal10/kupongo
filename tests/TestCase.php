<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Memastikan seluruh pengujian otomatis hanya berjalan pada database kupongo_test,
     * untuk melindungi integritas database utama kupongo.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $activeDb = config('database.connections.' . config('database.default') . '.database');
        if ($activeDb === 'kupongo') {
            $this->fail('PENGUJIAN DIHENTIKAN: Terdeteksi menggunakan database utama "kupongo". Seluruh pengujian wajib menggunakan database terpisah "kupongo_test" guna melindungi data operasional.');
        }
    }
}
