<?php

namespace App\Service;

class RandomService {
    public function generateRandomNumber(mixed $from, mixed $to) : mixed {
        return rand($from, $to);
    }
}