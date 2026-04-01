<?php

namespace App\Enums;

enum ModuleEnum: string
{
    case CATEGORY = 'category';
    case INSTRUMENT = 'instrument';
    case REVIEW = 'review';
    case GUARANTEE = 'guarantee';
    case RENTAL = 'rental';
    case AUTH = 'auth';
    case CONDITION = 'instrument condition';
    case PENALTY = 'penalty';
    case USER = 'user';
}
