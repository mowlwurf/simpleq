<?php


class Command
{
    CONST QUEUE_CHECK         = 'simpleq:queue:check';
    CONST QUEUE_CLEAR         = 'simpleq:queue:clear';
    CONST QUEUE_GENERATE      = 'simpleq:queue:create';
    CONST QUEUE_HISTORY       = 'simpleq:queue:history';
    CONST QUEUE_HISTORY_CLEAR = 'simpleq:queue:clear:history';

    CONST SCHEDULER_CLEAR         = 'simpleq:scheduler:clear';
    CONST SCHEDULER_INIT          = 'simpleq:scheduler:init';
    CONST SCHEDULER_START         = 'simpleq:scheduler:start';
    CONST SCHEDULER_STOP          = 'simpleq:scheduler:stop';
    CONST SCHEDULER_STATUS        = 'simpleq:scheduler:status';
    CONST SCHEDULER_HISTORY_CLEAR = 'simpleq:scheduler:clear:history';
    CONST SCHEDULER_HISTORY       = 'simpleq:scheduler:history';

    CONST WORKER_RUN    = 'simpleq:worker:run';
    CONST WORKER_STATUS = 'simpleq:worker:status';
}