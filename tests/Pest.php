<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Vinksyunit\NotTodayHoney\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);
uses(RefreshDatabase::class)->in('Feature');
