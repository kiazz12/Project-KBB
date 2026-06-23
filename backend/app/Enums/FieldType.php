<?php

namespace App\Enums;

enum FieldType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Email = 'email';
    case Number = 'number';
    case Date = 'date';
    case Time = 'time';
    case Select = 'select';
    case Radio = 'radio';
    case Checkbox = 'checkbox';
    case File = 'file';
    case Heading = 'heading';
    case Paragraph = 'paragraph';
    case Signature = 'signature';
}
