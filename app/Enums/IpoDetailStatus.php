<?php

namespace App\Enums;

enum IpoDetailStatus: string
{
    case Upcoming = 'upcoming';
    case Opened = 'opened';
    case Closed = 'closed';
    case Allotted = 'allotted';
}
