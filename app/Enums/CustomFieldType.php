<?php

namespace App\Enums;

enum CustomFieldType: string
{
    case Text = 'text';
    case Number = 'number';
    case Select = 'select';
    case Checkbox = 'checkbox';
    case Textarea = 'textarea';
}
