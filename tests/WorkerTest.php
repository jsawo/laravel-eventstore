<?php

namespace DigitalRisks\LaravelEventStore\Tests;

use DigitalRisks\LaravelEventStore\Console\Commands\EventStoreWorker;
use DigitalRisks\LaravelEventStore\Tests\Traits\InteractsWithEventStore;
use DigitalRisks\LaravelEventStore\Tests\Traits\MakesEventRecords;
use Illuminate\Support\Facades\Event;
use Rxnet\EventStore\Record\EventRecord;
use DigitalRisks\LaravelEventStore\Tests\Fixtures\TestEvent;

class WorkerTest extends TestCase
{
    use InteractsWithEventStore, MakesEventRecords;

    public function test_it_dispatches_an_event_from_a_subscribed_event()
    {
        // Arrange.
        Event::fake();
        $worker = resolve(EventStoreWorker::class);
        $event = $this->makeEventRecord('event_with_no_class', ['hello' => 'world']);

        // Act.
        $worker->dispatch($event);

        // Assert.
        Event::assertDispatched('event_with_no_class', function ($type, EventRecord $event) {
            $this->assertEquals(['hello' => 'world'], $event->getData());

            return true;
        });
    }

    public function test_it_dispatches_a_classed_event_from_a_subscribed_event()
    {
        // Arrange.
        Event::fake();
        $worker = resolve(EventStoreWorker::class);
        $event = $this->makeEventRecord('test_event', ['hello' => 'world']);
        config([
            'eventstore.event_to_class' => function ($event) {
                return 'DigitalRisks\LaravelEventStore\Tests\Fixtures\TestEvent';
            }
        ]);

        // Act.
        $worker->dispatch($event);

        // Assert.
        Event::assertDispatched(TestEvent::class, function (TestEvent $event) {
            $this->assertEquals('world', $event->hello);

            return true;
        });
    }
}
