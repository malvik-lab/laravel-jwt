<?php

namespace MalvikLab\LaravelJwt\Enum;

enum TokenTypeEnum: string
{
    case ACCESS_TOKEN = 'ACCESS_TOKEN';
    case REFRESH_TOKEN = 'REFRESH_TOKEN';
}
