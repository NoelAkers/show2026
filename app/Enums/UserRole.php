<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Judge = 'judge';
    case Helper = 'helper';
    case Exhibitor = 'exhibitor';
    case Steward = 'steward';
}
