<?php

namespace App\Enums;

enum PersonRole: string
{
    case Director        = 'director';
    case Actor           = 'actor';
    case VoiceActor      = 'voice-actor';
    case Producer        = 'producer';
    case Screenwriter    = 'screenwriter';
    case Operator        = 'operator';
    case Artist          = 'artist';
    case Editor          = 'editor';
    case Composer        = 'composer';
    case SoundDirector   = 'sound-director';
    case DubbingDirector = 'dubbing-director';
    case DubbingActor    = 'dubbing-actor';
    case Translator       = 'translator';
}
