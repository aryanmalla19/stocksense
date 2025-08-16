<?php

namespace App\Enums;

enum IpoApplicationStatus: string
{
    case Pending = 'pending';
    case Allotted = 'allotted';
    case NotAllotted = 'not_allotted';
}
